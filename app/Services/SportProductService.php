<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{DB, Validator, Storage};
use Helper;
use App\Models\{SportProduct, SportProductCategory, SportProductVariant, SportProductStock, SportProductStockLog, SportProductCart, SportProductOrder, Voucher};

class SportProductService
{
    // ─── API Methods ──────────────────────────────────────────────────────────

    public static function getCategories($request)
    {
        $categories = SportProductCategory::where('status', 10)
            ->with('children:id,parent_id,name,slug,image,sequence')
            ->whereNull('parent_id')
            ->orderBy('sequence')
            ->get(['id', 'name', 'slug', 'image', 'sequence']);

        $categories->each(function ($cat) {
            $cat->append(['encrypted_id', 'image_path']);
            $cat->children->each(fn($c) => $c->append('encrypted_id'));
        });

        return $categories;
    }

    public static function getProducts($request)
    {
        $query = SportProduct::where('status', 10)
            ->with([
                'category:id,parent_id,name,slug',
                'sports:id,name,slug,icon',
                'activeVariants:id,sport_product_id,name,price,compare_price,specs,image,sequence',
                'activeVariants.stock:id,variant_id,quantity,reserved_quantity',
            ]);

        if ($request->filled('category_id')) {
            $catId    = Helper::decode($request->category_id);
            $childIds = SportProductCategory::where('parent_id', $catId)->pluck('id')->toArray();
            $catIds   = array_merge([$catId], $childIds);
            $query->whereIn('category_id', $catIds);
        }

        if ($request->filled('sport_id')) {
            $sportId = Helper::decode($request->sport_id);
            $query->whereHas('sports', fn($q) => $q->where('sports.id', $sportId));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->whereHas('activeVariants', function ($q) use ($request) {
                if ($request->filled('min_price')) $q->where('price', '>=', $request->min_price);
                if ($request->filled('max_price')) $q->where('price', '<=', $request->max_price);
            });
        }

        if ($request->boolean('in_stock')) {
            $query->whereHas('activeVariants.stock', fn($q) =>
                $q->whereRaw('quantity - reserved_quantity > 0')
            );
        }

        // Grouped mode: return products grouped by sub-category (no pagination)
        if ($request->boolean('grouped') && $request->filled('category_id')) {
            $catId    = Helper::decode($request->category_id);
            $children = SportProductCategory::where('parent_id', $catId)
                ->where('status', 10)
                ->orderBy('sequence')
                ->get(['id', 'name', 'slug', 'image', 'sequence']);

            if ($children->isNotEmpty()) {
                $grouped = $children->map(function ($child) use ($query) {
                    $child->append(['encrypted_id', 'image_path']);
                    $childProducts = (clone $query)->where('category_id', $child->id)->get();
                    return [
                        'category' => [
                            'id'    => $child->encrypted_id,
                            'name'  => $child->name,
                            'slug'  => $child->slug,
                            'image' => $child->image_path,
                        ],
                        'products' => $childProducts->map(fn($p) => self::formatProduct($p)),
                    ];
                })->filter(fn($g) => count($g['products']) > 0)->values();

                return ['grouped' => true, 'data' => $grouped];
            }
        }

        switch ($request->input('sort')) {
            case 'price_asc':
                $query->orderByRaw('(SELECT MIN(price) FROM sport_product_variants WHERE sport_product_id = sport_products.id AND status = 10) ASC');
                break;
            case 'price_desc':
                $query->orderByRaw('(SELECT MIN(price) FROM sport_product_variants WHERE sport_product_id = sport_products.id AND status = 10) DESC');
                break;
            case 'newest':
                $query->orderByDesc('id');
                break;
            default:
                $query->orderBy('sequence')->orderBy('id');
                break;
        }

        $perPage  = min((int) $request->input('per_page', 15), 50);
        $products = $query->paginate($perPage)->withQueryString();

        $products->getCollection()->transform(fn($p) => self::formatProduct($p));

        return $products;
    }

    public static function getProductDetails($request)
    {
        $id      = Helper::decode($request->id);
        $product = SportProduct::where('status', 10)
            ->with([
                'category:id,name,slug',
                'sports:id,name,slug,icon',
                'activeVariants:id,sport_product_id,name,sku,price,compare_price,specs,image,sequence,status',
                'activeVariants.stock:id,variant_id,quantity,reserved_quantity',
            ])
            ->find($id);

        if (!$product) {
            return null;
        }

        return self::formatProduct($product, true);
    }

    public static function getCategoryDetails($request)
    {
        $id       = Helper::decode($request->id);
        $category = SportProductCategory::where('status', 10)
            ->with('children:id,parent_id,name,slug,image,sequence')
            ->find($id);

        if (!$category) {
            return null;
        }

        $category->append('encrypted_id');
        $category->children->each(fn($c) => $c->append('encrypted_id'));

        return $category;
    }

    public static function getCart($request)
    {
        $user = auth('user')->user();
        $cart = SportProductCart::with([
            'items.variant:id,sport_product_id,name,price,compare_price,image',
            'items.variant.product:id,name,images',
            'items.variant.stock:id,variant_id,quantity,reserved_quantity',
            'voucher',
        ])->firstOrCreate(['user_id' => $user->id]);

        $items = $cart->items->map(function ($item) {
            $v     = $item->variant;
            $stock = $v?->stock;
            return [
                'cart_item_id'  => Helper::encode($item->id),
                'variant_id'    => $v?->encrypted_id,
                'product_name'  => $v?->product?->name,
                'variant_name'  => $v?->name,
                'image'         => $v?->image_path,
                'price'         => number_format((float) ($v?->price ?? 0), 2, '.', ''),
                'compare_price' => $v?->compare_price ? number_format((float) $v->compare_price, 2, '.', '') : null,
                'quantity'      => $item->quantity,
                'total_price'   => number_format(round((float) ($v?->price ?? 0) * $item->quantity, 2), 2, '.', ''),
                'available'     => max(0, ($stock?->quantity ?? 0) - ($stock?->reserved_quantity ?? 0)),
            ];
        });

        $subtotal     = round($items->sum(fn($i) => (float) $i['total_price']), 2);
        $shippingCost = 10.00;

        // Voucher / discount
        $discount    = 0.00;
        $voucherCode = null;
        $v           = $cart->voucher;

        if ($v && $v->status == 10 && $subtotal >= ($v->min_spend ?? 0)) {
            $discount = $v->discount_type == 1
                ? round($subtotal * $v->discount_amount / 100, 2)
                : (float) $v->discount_amount;
            $discount    = min($discount, $subtotal);
            $voucherCode = $v->promo_code;
        }

        $sst   = round(($subtotal - $discount) * 0.06, 2);
        $total = round($subtotal - $discount + $sst + $shippingCost, 2);

        return [
            'cart_id' => Helper::encode($cart->id),
            'cart'    => [
                'id'                    => Helper::encode($cart->id),
                'voucher_code'          => $voucherCode,
                'items'                 => $items->values(),
                'subtotal'              => number_format($subtotal, 2, '.', ''),
                'discount_amount'       => number_format($discount, 2, '.', ''),
                'amount_after_discount' => number_format($subtotal - $discount, 2, '.', ''),
                'tax_amount'            => number_format($sst, 2, '.', ''),
                'tax_breakdown'         => [
                    ['name' => 'sst', 'rate' => 6, 'amount' => number_format($sst, 2, '.', '')],
                ],
                'shipping_cost'         => number_format($shippingCost, 2, '.', ''),
                'grand_total'           => number_format($total, 2, '.', ''),
                'total_items'           => $items->sum(fn($i) => $i['quantity']),
            ],
        ];
    }

    public static function addToCart($request)
    {
        $user      = auth('user')->user();
        $variantId = Helper::decode($request->variant_id);
        $variant   = SportProductVariant::with('stock')->where('status', 10)->find($variantId);

        if (!$variant) {
            return ['status' => 'error', 'code' => 404, 'message' => 'Variant not found'];
        }

        $available = max(0, ($variant->stock?->quantity ?? 0) - ($variant->stock?->reserved_quantity ?? 0));

        if ($available < $request->quantity) {
            return ['status' => 'error', 'code' => 422, 'message' => 'Insufficient stock'];
        }

        $cart = SportProductCart::firstOrCreate(['user_id' => $user->id]);
        $item = $cart->items()->where('variant_id', $variantId)->first();

        if ($item) {
            $newQty = $item->quantity + $request->quantity;
            if ($available < $newQty) {
                return ['status' => 'error', 'code' => 422, 'message' => 'Insufficient stock'];
            }
            $item->update(['quantity' => $newQty]);
        } else {
            $cart->items()->create(['variant_id' => $variantId, 'quantity' => $request->quantity]);
        }

        return ['status' => 'success', 'code' => 200, 'message' => 'Added to cart', 'data' => self::getCart($request)];
    }

    public static function updateCartItem($request)
    {
        $user = auth('user')->user();
        $cart = SportProductCart::where('user_id', $user->id)->first();
        $item = $cart?->items()->where('id', Helper::decode($request->item_id))->with('variant.stock')->first();

        if (!$item) {
            return ['status' => 'error', 'code' => 404, 'message' => 'Cart item not found'];
        }

        if (!$item->variant) {
            return ['status' => 'error', 'code' => 422, 'message' => 'Variant no longer available'];
        }

        $available = max(0, ($item->variant->stock?->quantity ?? 0) - ($item->variant->stock?->reserved_quantity ?? 0));

        if ($available < $request->quantity) {
            return ['status' => 'error', 'code' => 422, 'message' => 'Insufficient stock'];
        }

        $item->update(['quantity' => $request->quantity]);

        return ['status' => 'success', 'code' => 200, 'message' => 'Cart updated', 'data' => self::getCart($request)];
    }

    public static function removeFromCart($request)
    {
        $user = auth('user')->user();
        $cart = SportProductCart::where('user_id', $user->id)->first();
        $cart?->items()->where('id', Helper::decode($request->item_id))->delete();

        return ['status' => 'success', 'code' => 200, 'message' => 'Item removed', 'data' => self::getCart($request)];
    }

    public static function clearCart($request)
    {
        $user = auth('user')->user();
        $cart = SportProductCart::where('user_id', $user->id)->first();
        $cart?->items()->delete();

        return ['status' => 'success', 'code' => 200, 'message' => 'Cart cleared', 'cart_id' => Helper::encode($cart?->id)];
    }

    public static function applyVoucher($request)
    {
        $user    = auth('user')->user();
        $voucher = Voucher::where('promo_code', $request->promo_code)
            ->where('status', 10)
            ->first();

        if (!$voucher) {
            return ['status' => 'error', 'code' => 404, 'message' => 'Voucher not found or expired'];
        }

        $cart     = SportProductCart::with('items.variant:id,price')->firstOrCreate(['user_id' => $user->id]);
        $subtotal = $cart->items->sum(fn($i) => round((float) ($i->variant?->price ?? 0) * $i->quantity, 2));

        if ($voucher->min_spend && $subtotal < (float) $voucher->min_spend) {
            return [
                'status'  => 'error',
                'code'    => 422,
                'message' => 'Minimum spend of RM ' . number_format($voucher->min_spend, 2) . ' required',
            ];
        }

        $cart->update(['voucher_id' => $voucher->id]);

        return ['status' => 'success', 'code' => 200, 'message' => 'Voucher applied', 'cart_id' => Helper::encode($cart->id)];
    }

    public static function removeVoucher($request)
    {
        $user = auth('user')->user();
        $cart = SportProductCart::where('user_id', $user->id)->first();
        $cart?->update(['voucher_id' => null]);

        return ['status' => 'success', 'code' => 200, 'message' => 'Voucher removed', 'cart_id' => Helper::encode($cart?->id)];
    }

    public static function getMyOrders($request)
    {
        $user    = auth('user')->user();
        $perPage = $request->input('per_page', 15);

        $orders = SportProductOrder::where('user_id', $user->id)
            ->with('items:id,order_id,variant_id,name,price,quantity,image')
            ->latest()
            ->paginate($perPage);

        $orders->getCollection()->transform(fn($o) => [
            'id'              => Helper::encode($o->id),
            'order_reference' => 'XP' . $o->created_at->format('ym') . str_pad($o->id, 6, '0', STR_PAD_LEFT),
            'status'          => match((int) $o->status) {
                SportProductOrder::STATUS_PENDING_PAYMENT => 'pending_payment',
                SportProductOrder::STATUS_COMPLETED       => 'completed',
                SportProductOrder::STATUS_CANCELLED       => 'cancelled',
                SportProductOrder::STATUS_EXPIRED         => 'expired',
                default                                   => 'unknown',
            },
            'total'           => (float) $o->total,
            'items_count'     => $o->items->count(),
            'created_at'      => $o->created_at,
        ]);

        return $orders;
    }

    public static function getOrderDetails($request)
    {
        $user  = auth('user')->user();
        $order = SportProductOrder::where('user_id', $user->id)
            ->with(['items:id,order_id,variant_id,name,price,quantity,image', 'voucher'])
            ->find(Helper::decode($request->id));

        if (!$order) {
            return null;
        }

        $subtotal     = (float) $order->subtotal;
        $discount     = (float) $order->discount;
        $shippingCost = 10.00;
        $sst          = round(($subtotal - $discount) * 0.06, 2);

        $delivery = [
            'name'     => $user->fullname,
            'phone'    => ($user->calling_code ? '+' . $user->calling_code . ' ' : '') . $user->phone_number,
            'address'  => trim(implode(', ', array_filter([$user->address_1, $user->address_2]))),
            'city'     => $user->city,
            'state'    => $user->state,
            'postcode' => $user->postcode,
        ];

        return [
            'id'              => Helper::encode($order->id),
            'order_reference' => 'XP' . $order->created_at->format('ym') . str_pad($order->id, 6, '0', STR_PAD_LEFT),
            'status'          => $order->status,
            'notes'           => $order->notes,
            'created_at'      => $order->created_at,
            'delivery'        => $delivery,
            'shipping'        => [
                'option' => 'standard',
                'label'  => 'Standard Delivery',
                'eta'    => '2-4 working days',
                'cost'   => $shippingCost,
            ],
            'voucher'         => $order->voucher ? [
                'promo_code'      => $order->voucher->promo_code,
                'discount_type'   => $order->voucher->discount_type,
                'discount_amount' => (float) $order->voucher->discount_amount,
            ] : null,
            'summary'         => [
                'subtotal'      => $subtotal,
                'discount'      => $discount,
                'sst'           => $sst,
                'shipping_cost' => $shippingCost,
                'total'         => (float) $order->total,
            ],
            'items'           => $order->items->map(fn($i) => [
                'variant_id' => Helper::encode($i->variant_id),
                'name'       => $i->name,
                'image'      => $i->image ? asset('storage/' . $i->image) : null,
                'price'      => (float) $i->price,
                'quantity'   => $i->quantity,
                'subtotal'   => round((float) $i->price * $i->quantity, 2),
            ]),
        ];
    }

    public static function createOrder($request)
    {
        $user  = auth('user')->user();
        $lines = [];

        $cart = SportProductCart::with(['voucher', 'items.variant.stock'])
            ->where('id', Helper::decode($request->cart_id))
            ->where('user_id', $user->id)
            ->first();

        if (!$cart) {
            return ['status' => 'error', 'code' => 404, 'message' => 'Cart not found.'];
        }

        if ($cart->items->isEmpty()) {
            return ['status' => 'error', 'code' => 422, 'message' => 'Your cart is empty.'];
        }

        $voucher = $cart->voucher && $cart->voucher->status == 10 ? $cart->voucher : null;

        foreach ($cart->items as $cartItem) {
            $variant = $cartItem->variant;

            if (!$variant || $variant->status != 10) {
                return ['status' => 'error', 'code' => 422, 'message' => 'A product in your cart is no longer available.'];
            }

            $available = max(0, ($variant->stock?->quantity ?? 0) - ($variant->stock?->reserved_quantity ?? 0));
            if ($available < $cartItem->quantity) {
                return ['status' => 'error', 'code' => 422, 'message' => "Insufficient stock for: {$variant->name}"];
            }

            $lines[] = [
                'variant_id' => $variant->id,
                'name'       => $variant->name,
                'price'      => $variant->price,
                'quantity'   => $cartItem->quantity,
                'image'      => $variant->image,
            ];
        }

        $subtotal     = array_sum(array_map(fn($l) => round((float) $l['price'] * $l['quantity'], 2), $lines));
        $discount     = 0.00;
        $shippingCost = 10.00;

        if ($voucher && $subtotal >= ($voucher->min_spend ?? 0)) {
            $discount = $voucher->discount_type == 1
                ? round($subtotal * $voucher->discount_amount / 100, 2)
                : (float) $voucher->discount_amount;
            $discount = min($discount, $subtotal);
        }

        $sst   = round(($subtotal - $discount) * 0.06, 2);
        $total = round($subtotal - $discount + $sst + $shippingCost, 2);

        // Prepare iPay88 credentials
        $ipay88Credentials = Helper::getIPay88Credentials();
        $merchantCode = $ipay88Credentials['merchant_code'];
        $merchantKey  = $ipay88Credentials['merchant_key'];
        $responseUrl  = $ipay88Credentials['response_url'];
        $backendUrl   = $ipay88Credentials['backend_url'];
        $currency     = $ipay88Credentials['currency'];

        $prodDesc    = 'Sport Product Order';
        $userName    = $user->fullname;
        $userEmail   = $user->email;
        $userContact = ($user->calling_code ? '+' . $user->calling_code . ' ' : '') . $user->phone_number;

        DB::beginTransaction();

        try {
            $order = SportProductOrder::create([
                'user_id'    => $user->id,
                'voucher_id' => $voucher?->id,
                'subtotal'   => $subtotal,
                'discount'   => $discount,
                'total'      => $total,
                'status'     => SportProductOrder::STATUS_PENDING_PAYMENT,
                'notes'      => $request->notes,
            ]);

            $orderNo = 'SPO-' . str_pad($order->id, 8, '0', STR_PAD_LEFT);

            $order->update([
                'order_no'            => $orderNo,
                'payment_gateway'     => 'ipay88',
                'payment_gateway_ref' => $orderNo,
                'payment_attempt'     => 1,
            ]);

            foreach ($lines as $line) {
                $order->items()->create($line);
                SportProductVariant::find($line['variant_id'])?->stock()
                    ->increment('reserved_quantity', $line['quantity']);
            }

            // Clear cart after successful checkout
            if ($cart) {
                $cart->items()->delete();
                $cart->update(['voucher_id' => null]);
            }

            // Generate iPay88 signature and payment data
            $signature   = self::generateIPay88Signature($merchantKey, $merchantCode, $orderNo, $total, $currency);
            $paymentData = [
                'MerchantCode'  => $merchantCode,
                'PaymentId'     => '',
                'RefNo'         => $orderNo,
                'Amount'        => number_format((float) $total, 2, '.', ''),
                'Currency'      => $currency,
                'ProdDesc'      => $prodDesc,
                'UserName'      => $userName,
                'UserEmail'     => $userEmail,
                'UserContact'   => $userContact,
                'Remark'        => $request->notes ?? '',
                'Lang'          => 'UTF-8',
                'Signature'     => $signature,
                'SignatureType' => 'HMACSHA512',
                'ResponseURL'   => $responseUrl,
                'BackendURL'    => $backendUrl,
            ];

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        $redirectUrl = Helper::getPaymentGatewayUrl('api/v1/payment/ipay88/sport-product/' . Helper::encode($order->id));

        return [
            'status' => 'success',
            'code'   => 201,
            'data'   => [
                'id'           => Helper::encode($order->id),
                'order_no'     => $orderNo,
                'created_at'   => $order->created_at,
                'total'        => $total,
                'payment_url'  => $redirectUrl,
                'payment_data' => $paymentData,
            ],
        ];
    }

    public static function cancelOrder($request)
    {
        $user  = auth('user')->user();
        $order = SportProductOrder::where('user_id', $user->id)
            ->with('items')
            ->find(Helper::decode($request->id));

        if (!$order) {
            return ['status' => 'error', 'code' => 404, 'message' => 'Order not found'];
        }

        if ($order->status !== SportProductOrder::STATUS_PENDING_PAYMENT) {
            return ['status' => 'error', 'code' => 422, 'message' => 'Only pending orders can be cancelled'];
        }

        DB::beginTransaction();

        try {
            foreach ($order->items as $item) {
                SportProductVariant::find($item->variant_id)?->stock()
                    ->decrement('reserved_quantity', $item->quantity);
            }

            $order->update(['status' => SportProductOrder::STATUS_CANCELLED]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return ['status' => 'success', 'code' => 200, 'message' => 'Order cancelled'];
    }

    public static function formatProduct(SportProduct $product, bool $detailed = false): array
    {
        $product->append('encrypted_id');

        $variants = $product->activeVariants->map(function ($v) use ($detailed) {
            $v->append('encrypted_id');
            $stock = $v->stock;
            $data  = [
                'id'            => $v->encrypted_id,
                'name'          => $v->name,
                'price'         => (float) $v->price,
                'compare_price' => $v->compare_price ? (float) $v->compare_price : null,
                'specs'         => $v->specs ?? [],
                'image'         => $v->image_path,
                'in_stock'      => ($stock?->quantity ?? 0) - ($stock?->reserved_quantity ?? 0) > 0,
                'available'     => max(0, ($stock?->quantity ?? 0) - ($stock?->reserved_quantity ?? 0)),
            ];
            if ($detailed) $data['sku'] = $v->sku;
            return $data;
        });

        $minPrice = $product->activeVariants->min('price');
        $maxPrice = $product->activeVariants->max('price');
        $inStock  = $variants->contains('in_stock', true);

        $data = [
            'id'            => $product->encrypted_id,
            'name'          => $product->name,
            'slug'          => $product->slug,
            'images'        => $product->image_paths,
            'price_range'   => $minPrice == $maxPrice
                ? (float) $minPrice
                : [(float) $minPrice, (float) $maxPrice],
            'in_stock'      => $inStock,
            'rating'        => null,
            'reviews_count' => 0,
            'category'      => $product->category ? [
                'id'   => $product->category->encrypted_id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'sports'        => $product->sports->map(fn($s) => [
                'id'   => $s->encrypted_id,
                'name' => $s->name,
                'icon' => $s->icon_path,
            ]),
            'variants'      => $variants,
        ];

        if ($detailed) {
            $data['description'] = $product->description;
        }

        return $data;
    }

    // ─── Listing ───────────────────────────────────────────────────────────────

    public static function allProducts($request)
    {
        $products = SportProduct::with([
                'category:id,name',
                'sports:id,name',
                'variants:id,sport_product_id,name,price,status',
            ])
            ->withCount('variants')
            ->select('sport_products.*');

        $filterObject = self::filter($request, $products);
        $model  = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ($request->input('order.0.column') != 0) {
            $dir = $request->input('order.0.dir');
            switch ($request->input('order.0.column')) {
                case 1: $model->orderBy('name', $dir); break;
                case 2: $model->orderBy('sequence', $dir); break;
                case 3: $model->orderBy('status', $dir); break;
            }
        } else {
            $model->orderBy('sequence')->orderBy('id');
        }

        $total = SportProduct::count();
        $count = $model->count();
        $limit = $request->length == -1 ? 1000000 : $request->length;
        $items = $model->skip($request->start)->take($limit)->get();

        $items->each(function ($product) {
            $product->append(['encrypted_id', 'image_paths']);
            $product->variants->each(fn($v) => $v->append(['encrypted_id']));
        });

        return response()->json([
            'products'        => $items,
            'draw'            => $request->draw,
            'recordsFiltered' => $filter ? $count : $total,
            'recordsTotal'    => $total,
        ]);
    }

    public static function oneProduct($request)
    {
        $id      = Helper::decode($request->id);
        $product = SportProduct::with([
            'category:id,name',
            'sports:id,name',
            'variants.stock',
        ])->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $product->append(['encrypted_id']);
        $product->variants->each(fn($v) => $v->append(['encrypted_id']));

        return response()->json($product);
    }

    // ─── Create ────────────────────────────────────────────────────────────────

    public static function createProduct($request)
    {
        $validator = Validator::make($request->all(), [
            'name'           => ['required', 'string', 'max:255'],
            'category_id'    => ['nullable', 'exists:sport_product_categories,id'],
            'sport_ids'      => ['nullable', 'array'],
            'sport_ids.*'    => ['exists:sports,id'],
            'description'    => ['nullable', 'string'],
            'sequence'       => ['nullable', 'integer'],
            'status'         => ['nullable', 'in:10,20'],
            'images'         => ['nullable', 'array'],
            'images.*'       => ['nullable', 'string'],
            'variants'       => ['nullable', 'array'],
            'variants.*.name'          => ['required_with:variants', 'string', 'max:255'],
            'variants.*.price'         => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.compare_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.sku'           => ['nullable', 'string', 'max:100'],
            'variants.*.initial_stock' => ['nullable', 'integer', 'min:0'],
        ]);

        $validator->validate();

        DB::beginTransaction();
        try {
            $slug = Str::slug($request->name);
            $orig = $slug;
            $i    = 1;
            while (SportProduct::where('slug', $slug)->exists()) {
                $slug = $orig . '-' . $i++;
            }

            $product = SportProduct::create([
                'category_id' => $request->filled('category_id') ? $request->category_id : null,
                'name'        => $request->name,
                'slug'        => $slug,
                'description' => $request->description,
                'sequence'    => $request->input('sequence', 0),
                'status'      => $request->input('status', 10),
            ]);

            // Images (pre-uploaded string paths from Dropzone)
            if ($request->filled('images')) {
                $product->images = array_values(array_filter((array) $request->input('images')));
                $product->save();
            }

            // Sports (many-to-many)
            if ($request->filled('sport_ids')) {
                $product->sports()->sync($request->sport_ids);
            }

            // Variants + initial stock
            if ($request->filled('variants')) {
                foreach ($request->variants as $i => $varData) {
                    $variant = SportProductVariant::create([
                        'sport_product_id' => $product->id,
                        'name'             => $varData['name'],
                        'sku'              => $varData['sku'] ?? null,
                        'price'            => $varData['price'],
                        'compare_price'    => $varData['compare_price'] ?? null,
                        'specs'            => isset($varData['specs']) ? $varData['specs'] : null,
                        'sequence'         => $i,
                        'status'           => $varData['status'] ?? 10,
                    ]);

                    $initialStock = (int) ($varData['initial_stock'] ?? 0);
                    $stock = SportProductStock::create([
                        'variant_id'        => $variant->id,
                        'quantity'          => $initialStock,
                        'reserved_quantity' => 0,
                    ]);

                    if ($initialStock > 0) {
                        SportProductStockLog::create([
                            'variant_id'          => $variant->id,
                            'quantity_change'     => $initialStock,
                            'quantity_after'      => $initialStock,
                            'type'                => 'restock',
                            'notes'               => 'Initial stock on product creation',
                            'created_by_admin_id' => auth('admin')->id(),
                        ]);
                    }

                    // Variant image (pre-uploaded string path or UploadedFile)
                    if (isset($varData['image'])) {
                        if ($varData['image'] instanceof \Illuminate\Http\UploadedFile) {
                            $variant->image = $varData['image']->store('sport-product-variants', ['disk' => 'public']);
                        } elseif (is_string($varData['image']) && $varData['image']) {
                            $variant->image = $varData['image'];
                        }
                        $variant->save();
                    }
                }
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage() . ' line: ' . $th->getLine()], 500);
        }

        return response()->json(['message' => __('template.record_created_successfully'), 'id' => $product->encrypted_id]);
    }

    // ─── Update ────────────────────────────────────────────────────────────────

    public static function updateProduct($request)
    {
        $id      = Helper::decode($request->id);
        $product = SportProduct::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'           => ['required', 'string', 'max:255'],
            'category_id'    => ['nullable', 'exists:sport_product_categories,id'],
            'sport_ids'      => ['nullable', 'array'],
            'sport_ids.*'    => ['exists:sports,id'],
            'description'    => ['nullable', 'string'],
            'sequence'       => ['nullable', 'integer'],
            'status'         => ['nullable', 'in:10,20'],
            'images.*'       => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:3072'],
        ]);

        $validator->validate();

        DB::beginTransaction();
        try {
            $product->category_id  = $request->filled('category_id') ? $request->category_id : null;
            $product->name         = $request->name;
            $product->description  = $request->description;
            $product->sequence     = $request->input('sequence', $product->sequence);
            $product->status       = $request->input('status', $product->status);

            // New images appended to existing
            if ($request->hasFile('images')) {
                $existing = $product->images ?? [];
                foreach ($request->file('images') as $img) {
                    $existing[] = $img->store('sport-products', ['disk' => 'public']);
                }
                $product->images = $existing;
            }

            $product->save();

            // Sports
            $product->sports()->sync($request->input('sport_ids', []));

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage() . ' line: ' . $th->getLine()], 500);
        }

        return response()->json(['message' => __('template.record_updated_successfully')]);
    }

    public static function removeProductImage($request)
    {
        $id      = Helper::decode($request->id);
        $product = SportProduct::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $index  = (int) $request->index;
        $images = $product->images ?? [];

        if (!isset($images[$index])) {
            return response()->json(['message' => 'Image not found.'], 404);
        }

        Storage::disk('public')->delete($images[$index]);
        array_splice($images, $index, 1);
        $product->images = array_values($images);
        $product->save();

        return response()->json(['message' => __('template.record_updated_successfully')]);
    }

    public static function updateProductStatus($request)
    {
        $id      = Helper::decode($request->id);
        $product = SportProduct::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $product->status = $request->status;
        $product->save();

        return response()->json(['message' => __('template.record_updated_successfully')]);
    }

    public static function deleteProduct($request)
    {
        $id      = Helper::decode($request->id);
        $product = SportProduct::with('variants')->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        DB::beginTransaction();
        try {
            // Delete images
            foreach ($product->images ?? [] as $img) {
                Storage::disk('public')->delete($img);
            }
            // Delete variant images
            foreach ($product->variants as $variant) {
                if ($variant->image) Storage::disk('public')->delete($variant->image);
            }

            $product->delete();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }

        return response()->json(['message' => __('template.record_deleted_successfully')]);
    }

    // ─── Variants ──────────────────────────────────────────────────────────────

    public static function createVariant($request)
    {
        $productId = Helper::decode($request->product_id);
        $product   = SportProduct::find($productId);

        if (!$product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'          => ['required', 'string', 'max:255'],
            'price'         => ['required', 'numeric', 'min:0'],
            'compare_price' => ['nullable', 'numeric', 'min:0'],
            'sku'           => ['nullable', 'string', 'max:100'],
            'initial_stock' => ['nullable', 'integer', 'min:0'],
            'image'         => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        $validator->validate();

        DB::beginTransaction();
        try {
            $specs = $request->input('specs');
            if (is_string($specs)) $specs = json_decode($specs, true);

            $variant = SportProductVariant::create([
                'sport_product_id' => $product->id,
                'name'             => $request->name,
                'sku'              => $request->sku,
                'price'            => $request->price,
                'compare_price'    => $request->compare_price,
                'specs'            => $specs,
                'sequence'         => $product->variants()->max('sequence') + 1,
                'status'           => $request->input('status', 10),
            ]);

            if ($request->hasFile('image')) {
                $variant->image = $request->file('image')->store('sport-product-variants', ['disk' => 'public']);
                $variant->save();
            } elseif ($request->filled('image') && is_string($request->input('image'))) {
                $variant->image = $request->input('image');
                $variant->save();
            }

            $initialStock = (int) $request->input('initial_stock', 0);
            SportProductStock::create([
                'variant_id'        => $variant->id,
                'quantity'          => $initialStock,
                'reserved_quantity' => 0,
            ]);

            if ($initialStock > 0) {
                SportProductStockLog::create([
                    'variant_id'          => $variant->id,
                    'quantity_change'     => $initialStock,
                    'quantity_after'      => $initialStock,
                    'type'                => 'restock',
                    'notes'               => 'Initial stock on variant creation',
                    'created_by_admin_id' => auth('admin')->id(),
                ]);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage() . ' line: ' . $th->getLine()], 500);
        }

        $variant->append(['encrypted_id']);
        $variant->load('stock');

        return response()->json(['message' => __('template.record_created_successfully'), 'variant' => $variant]);
    }

    public static function updateVariant($request)
    {
        $id      = Helper::decode($request->id);
        $variant = SportProductVariant::find($id);

        if (!$variant) {
            return response()->json(['message' => 'Variant not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'          => ['required', 'string', 'max:255'],
            'price'         => ['required', 'numeric', 'min:0'],
            'compare_price' => ['nullable', 'numeric', 'min:0'],
            'sku'           => ['nullable', 'string', 'max:100'],
            'image'         => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'status'        => ['nullable', 'in:10,20'],
        ]);

        $validator->validate();

        DB::beginTransaction();
        try {
            $variant->name          = $request->name;
            $variant->sku           = $request->sku;
            $variant->price         = $request->price;
            $variant->compare_price = $request->compare_price;
            $variant->status        = $request->input('status', $variant->status);

            if ($request->has('specs')) {
                $specs = $request->input('specs');
                $variant->specs = is_string($specs) ? json_decode($specs, true) : $specs;
            }

            if ($request->hasFile('image')) {
                if ($variant->image) Storage::disk('public')->delete($variant->image);
                $variant->image = $request->file('image')->store('sport-product-variants', ['disk' => 'public']);
            } elseif ($request->filled('image') && is_string($request->input('image'))) {
                if ($variant->image && $variant->image !== $request->input('image')) {
                    Storage::disk('public')->delete($variant->image);
                }
                $variant->image = $request->input('image');
            }

            $variant->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage() . ' line: ' . $th->getLine()], 500);
        }

        return response()->json(['message' => __('template.record_updated_successfully')]);
    }

    public static function deleteVariant($request)
    {
        $id      = Helper::decode($request->id);
        $variant = SportProductVariant::find($id);

        if (!$variant) {
            return response()->json(['message' => 'Variant not found.'], 404);
        }

        DB::beginTransaction();
        try {
            if ($variant->image) Storage::disk('public')->delete($variant->image);
            $variant->delete();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }

        return response()->json(['message' => __('template.record_deleted_successfully')]);
    }

    public static function removeVariantImage($request)
    {
        $id      = Helper::decode($request->id);
        $variant = SportProductVariant::find($id);

        if (!$variant) {
            return response()->json(['message' => 'Variant not found.'], 404);
        }

        if ($variant->image) {
            Storage::disk('public')->delete($variant->image);
            $variant->image = null;
            $variant->save();
        }

        return response()->json(['message' => __('template.record_updated_successfully')]);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    private static function filter($request, $model)
    {
        $filter = false;

        if (!empty($request->status)) {
            $model->where('status', $request->status);
            $filter = true;
        }

        if (!empty($request->category_id)) {
            $model->where('category_id', $request->category_id);
            $filter = true;
        }

        if (!empty($request->sport_id)) {
            $model->whereHas('sports', fn($q) => $q->where('sports.id', $request->sport_id));
            $filter = true;
        }

        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $model->where('name', 'like', "%{$search}%");
            $filter = true;
        }

        return ['filter' => $filter, 'model' => $model];
    }

    /**
     * Generate iPay88 HMACSHA512 signature
     */
    private static function generateIPay88Signature($merchantKey, $merchantCode, $refNo, $amount, $currency)
    {
        $amount = number_format((float) $amount, 2, '.', '');
        $amount = strtr($amount, ['.' => '', ',' => '']);
        $source = $merchantKey . $merchantCode . $refNo . $amount . $currency;
        return hash_hmac('sha512', $source, $merchantKey);
    }
}
