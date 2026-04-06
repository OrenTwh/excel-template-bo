<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\SportProductService;

class SportProductController extends Controller
{
    /**
     * Get Product Categories
     *
     * Returns top-level sport product categories with their immediate children.
     *
     * @group Sport Product API
     *
     */
    public function getCategories(Request $request)
    {
        try {
            $categories = SportProductService::getCategories($request);

            return response()->json(['status' => 'success', 'data' => $categories]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to retrieve categories'], 500);
        }
    }

    /**
     * Get Products
     *
     * Returns a paginated list of active sport products with variant and stock info.
     *
     * @group Sport Product API
     *
     * @queryParam per_page    integer Number of records per page (max 50), default 15. Example: 15
     * @queryParam category_id string  Encrypted category ID. Child categories are included automatically. Example: E2
     * @queryParam sport_id    string  Encrypted sport ID to filter products by sport. Example: E2
     * @queryParam search      string  Search by product name. Example: badminton racket
     * @queryParam min_price   number  Minimum variant price filter. Example: 10.00
     * @queryParam max_price   number  Maximum variant price filter. Example: 100.00
     * @queryParam in_stock    boolean Filter to in-stock products only. Example: true
     * @queryParam sort        string  Sort order: price_asc, price_desc, newest. Defaults to sequence. Example: price_asc
     * @queryParam grouped     boolean When true and category_id is a parent, returns products grouped by sub-category (no pagination). Example: true
     *
     */
    public function getProducts(Request $request)
    {
        try {
            $products = SportProductService::getProducts($request);

            return response()->json(['status' => 'success', 'data' => $products]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to retrieve products'], 500);
        }
    }

    /**
     * Get Product Details
     *
     * Returns full details for a single active product including variants, SKUs, and stock.
     *
     * @group Sport Product API
     *
     * @queryParam id string required Encrypted product ID. Example: E2
     *
     */
    public function getProductDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        try {
            $product = SportProductService::getProductDetails($request);

            if (!$product) {
                return response()->json(['status' => 'error', 'message' => 'Product not found'], 404);
            }

            return response()->json(['status' => 'success', 'data' => $product]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to retrieve product'], 500);
        }
    }

    /**
     * Get Category Details
     *
     * Returns a single product category with its immediate child categories.
     *
     * @group Sport Product API
     *
     * @queryParam id string required Encrypted category ID. Example: E2
     *
     */
    public function getCategoryDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        try {
            $category = SportProductService::getCategoryDetails($request);

            if (!$category) {
                return response()->json(['status' => 'error', 'message' => 'Category not found'], 404);
            }

            return response()->json(['status' => 'success', 'data' => $category]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to retrieve category'], 500);
        }
    }

    // ── Cart ──────────────────────────────────────────────────────────────────

    /**
     * Get Cart
     *
     * Returns the authenticated user's cart with items, variant details, and stock availability.
     *
     * @group Sport Product API
     * @authenticated
     *
     */
    public function getCart(Request $request)
    {
        try {
            $data = SportProductService::getCart($request);

            return response()->json(['status' => 'success', 'message' => 'Get Cart Success', 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to retrieve cart'], 500);
        }
    }

    /**
     * Add to Cart
     *
     * Adds a product variant to the authenticated user's cart.
     * If the variant already exists in the cart, its quantity is incremented.
     *
     * @group Sport Product API
     * @authenticated
     *
     * @bodyParam variant_id string required Encrypted variant ID. Example: E2
     * @bodyParam quantity   integer required Quantity to add (min 1). Example: 1
     *
     */
    public function addToCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'variant_id' => ['required'],
            'quantity'   => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        try {
            $result = SportProductService::addToCart($request);

            if ($result['status'] === 'error') {
                return response()->json(['status' => 'error', 'message' => $result['message']], $result['code']);
            }

            return response()->json(['status' => 'success', 'message' => $result['message'], 'data' => $result['data']]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to add to cart'], 500);
        }
    }

    /**
     * Update Cart Item
     *
     * Updates the quantity of an existing item in the authenticated user's cart.
     *
     * @group Sport Product API
     * @authenticated
     *
     * @bodyParam item_id  string  required Encrypted cart item ID. Example: E2
     * @bodyParam quantity integer required New quantity (min 1). Example: 2
     *
     */
    public function updateCartItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id'  => ['required'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        try {
            $result = SportProductService::updateCartItem($request);

            if ($result['status'] === 'error') {
                return response()->json(['status' => 'error', 'message' => $result['message']], $result['code']);
            }

            return response()->json(['status' => 'success', 'message' => $result['message'], 'data' => $result['data']]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to update cart'], 500);
        }
    }

    /**
     * Remove Cart Item
     *
     * Removes a single item from the authenticated user's cart.
     *
     * @group Sport Product API
     * @authenticated
     *
     * @bodyParam item_id string required Encrypted cart item ID. Example: E2
     *
     */
    public function removeFromCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        try {
            $result = SportProductService::removeFromCart($request);

            return response()->json(['status' => 'success', 'message' => $result['message'], 'data' => $result['data']]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to remove item'], 500);
        }
    }

    /**
     * Clear Cart
     *
     * Removes all items from the authenticated user's cart.
     *
     * @group Sport Product API
     * @authenticated
     *
     */
    public function clearCart(Request $request)
    {
        try {
            $result = SportProductService::clearCart($request);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to clear cart'], 500);
        }
    }

    /**
     * Apply Voucher
     *
     * Applies a promo code voucher to the authenticated user's cart.
     * Validates minimum spend and voucher status before attaching.
     *
     * @group Sport Product API
     * @authenticated
     *
     * @bodyParam promo_code string required Promo code to apply. Example: SAVE10
     *
     */
    public function applyVoucher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'promo_code' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        try {
            $result = SportProductService::applyVoucher($request);

            if ($result['status'] === 'error') {
                return response()->json(['status' => 'error', 'message' => $result['message']], $result['code']);
            }

            return response()->json(['status' => 'success', 'message' => $result['message']]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to apply voucher'], 500);
        }
    }

    /**
     * Remove Voucher
     *
     * Removes the applied voucher from the authenticated user's cart.
     *
     * @group Sport Product API
     * @authenticated
     *
     */
    public function removeVoucher(Request $request)
    {
        try {
            $result = SportProductService::removeVoucher($request);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to remove voucher'], 500);
        }
    }

    // ── Orders ────────────────────────────────────────────────────────────────

    /**
     * Get My Orders
     *
     * Returns a paginated list of the authenticated user's orders with item counts.
     *
     * @group Sport Product API
     * @authenticated
     *
     * @queryParam per_page integer Number of records per page, default 15. Example: 15
     *
     */
    public function getMyOrders(Request $request)
    {
        try {
            $orders = SportProductService::getMyOrders($request);

            return response()->json(['status' => 'success', 'data' => $orders]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to retrieve orders'], 500);
        }
    }

    /**
     * Get Order Details
     *
     * Returns full details for a single order belonging to the authenticated user,
     * including all line items.
     *
     * @group Sport Product API
     * @authenticated
     *
     * @queryParam id string required Encrypted order ID. Example: E2
     *
     */
    public function getOrderDetails(Request $request)
    {
        try {
            $order = SportProductService::getOrderDetails($request);

            if (!$order) {
                return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
            }

            return response()->json(['status' => 'success', 'data' => $order]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to retrieve order'], 500);
        }
    }

    /**
     * Create Order
     *
     * Checks out from the user's cart. Items and any applied voucher are taken
     * directly from the cart. The cart is cleared on success.
     *
     * @group Sport Product API
     * @authenticated
     *
     * @bodyParam cart_id  string  required Encrypted cart ID. Example: E2
     * @bodyParam notes    string  Optional order notes (max 500 chars).
     *
     */
    public function createOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart_id' => ['required', 'string'],
            'notes'   => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        try {
            $result = SportProductService::createOrder($request);

            if ($result['status'] === 'error') {
                return response()->json(['status' => 'error', 'message' => $result['message']], $result['code']);
            }

            return response()->json(['status' => 'success', 'message' => 'Order created', 'data' => $result['data']], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'line' => $e->getLine(), 'file' => $e->getFile()], 500);
        }
    }

    /**
     * Cancel Order
     *
     * Cancels a pending order and releases the reserved stock for all line items.
     * Only orders with status "pending" can be cancelled.
     *
     * @group Sport Product API
     * @authenticated
     *
     * @bodyParam id string required Encrypted order ID. Example: E2
     *
     */
    public function cancelOrder(Request $request)
    {
        try {
            $result = SportProductService::cancelOrder($request);

            if ($result['status'] === 'error') {
                return response()->json(['status' => 'error', 'message' => $result['message']], $result['code']);
            }

            return response()->json(['status' => 'success', 'message' => $result['message']]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to cancel order'], 500);
        }
    }
}
