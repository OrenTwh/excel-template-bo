<?php

namespace App\Services;

use Illuminate\Support\Facades\{DB, Validator};
use App\Models\{SportProductOrder, SportProductOrderItem, SportProductVariant};
use Helper;
use Carbon\Carbon;

class SportProductOrderService
{
    const STATUS_PENDING_PAYMENT = 1;
    const STATUS_COMPLETED       = 10;
    const STATUS_CANCELLED       = 20;
    const STATUS_EXPIRED         = 21;

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDING_PAYMENT => __('sport_product.status_pending_payment'),
            self::STATUS_COMPLETED       => __('sport_product.status_completed'),
            self::STATUS_CANCELLED       => __('sport_product.status_cancelled'),
            self::STATUS_EXPIRED         => __('sport_product.status_expired'),
        ];
    }

    // ── DataTable list ─────────────────────────────────────────────────────────
    public static function allOrders($request)
    {
        $query = SportProductOrder::with(['user:id,fullname,email', 'items:id,order_id,name,quantity,price'])
            ->select('sport_product_orders.*');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn($u) => $u->where('fullname', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%"));
            });
        }

        // Ordering
        $orderCol = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        match ((int) $orderCol) {
            2 => $query->orderBy('created_at', $orderDir),
            3 => $query->orderBy('total', $orderDir),
            4 => $query->orderBy('status', $orderDir),
            default => $query->latest(),
        };

        $total    = $query->count();
        $start    = (int) $request->input('start', 0);
        $length   = (int) $request->input('length', 10);
        $orders   = $query->skip($start)->take($length)->get();

        $data = $orders->map(function ($order) {
            return [
                'encrypted_id' => Helper::encode($order->id),
                'user'         => $order->user ? [
                    'fullname' => $order->user->fullname,
                    'email'    => $order->user->email,
                ] : null,
                'items_count'  => $order->items->count(),
                'subtotal'     => (float) $order->subtotal,
                'discount'     => (float) $order->discount,
                'total'        => (float) $order->total,
                'status'       => $order->status,
                'created_at'   => $order->created_at,
            ];
        });

        return response()->json([
            'draw'            => (int) $request->draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'orders'          => $data,
        ]);
    }

    // ── Single order with items ────────────────────────────────────────────────
    public static function oneOrder($request)
    {
        $id    = Helper::decode($request->id);
        $order = SportProductOrder::with([
            'user:id,fullfullname,email,phone_number,calling_code',
            'items',
            'voucher:id,promo_code,discount_type,discount_amount',
        ])->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json([
            'encrypted_id' => Helper::encode($order->id),
            'user'         => $order->user ? [
                'fullname'     => $order->user->fullname,
                'email'        => $order->user->email,
                'phone_number' => ($order->user->calling_code ?? '') . ' ' . ($order->user->phone_number ?? ''),
            ] : null,
            'status'       => $order->status,
            'subtotal'     => (float) $order->subtotal,
            'discount'     => (float) $order->discount,
            'total'        => (float) $order->total,
            'notes'        => $order->notes,
            'voucher_code' => $order->voucher?->code,
            'created_at'   => $order->created_at,
            'items'        => $order->items->map(fn($i) => [
                'encrypted_variant_id' => Helper::encode($i->variant_id),
                'name'                 => $i->name,
                'price'                => (float) $i->price,
                'quantity'             => $i->quantity,
                'subtotal'             => round((float) $i->price * $i->quantity, 2),
                'image'                => $i->image ? asset('storage/' . $i->image) : null,
            ]),
        ]);
    }

    // ── Update status ──────────────────────────────────────────────────────────
    public static function updateOrderStatus($request)
    {
        Validator::make($request->all(), [
            'id'     => ['required'],
            'status' => ['required', 'integer', 'in:10,20,30,40'],
        ])->validate();

        $id    = Helper::decode($request->id);
        $order = SportProductOrder::with('items')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $newStatus = (int) $request->status;
        $oldStatus = (int) $order->status;

        DB::beginTransaction();
        try {
            // Release stock reservation when cancelled
            if (in_array($newStatus, [self::STATUS_CANCELLED, self::STATUS_EXPIRED]) && !in_array($oldStatus, [self::STATUS_CANCELLED, self::STATUS_EXPIRED])) {
                foreach ($order->items as $item) {
                    SportProductVariant::find($item->variant_id)?->stock()
                        ->decrement('reserved_quantity', $item->quantity);
                }
            }

            $order->update(['status' => $newStatus]);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], 500);
        }

        return response()->json([
            'message' => __('template.x_updated', ['title' => 'Order status']),
        ]);
    }
}
