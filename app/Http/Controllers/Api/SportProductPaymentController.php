<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SportProductOrder;
use App\Models\SportProductVariant;
use Helper;

class SportProductPaymentController extends Controller
{
    /**
     * Render the iPay88 payment page for a sport product order.
     * If no real credentials are configured, redirects to the mock payment page.
     */
    public function paymentPage($encryptedId)
    {
        $id    = Helper::decode($encryptedId);
        $order = SportProductOrder::with('user')->find($id);

        if (!$order || $order->status !== SportProductOrder::STATUS_PENDING_PAYMENT) {
            abort(404, 'Order not found or no longer payable.');
        }

        // Redirect to mock page when no real credentials are configured
        if (!config('services.ipay88.merchant_code') || !config('services.ipay88.merchant_key')) {
            return redirect(url("api/v1/payment/ipay88/sport-product/{$encryptedId}/mock"));
        }

        $user = $order->user;

        $merchantCode = config('services.ipay88.merchant_code');
        $merchantKey  = config('services.ipay88.merchant_key');
        $gatewayUrl   = config('services.ipay88.payment_url', 'https://payment.ipay88.com.my/epayment/entry.asp');
        $responseUrl  = config('services.ipay88.response_url', url('api/v1/payment/ipay88/sport-product/response'));
        $backendUrl   = config('services.ipay88.backend_url',  url('api/v1/payment/ipay88/sport-product/callback'));
        $currency     = config('services.ipay88.currency', 'MYR');

        $refNo  = $order->order_no;
        $amount = (float) $order->total;

        $signature = $this->generateIPay88Signature($merchantKey, $merchantCode, $refNo, $amount, $currency);

        $paymentData = [
            'MerchantCode'  => $merchantCode,
            'PaymentId'     => '',
            'RefNo'         => $refNo,
            'Amount'        => number_format($amount, 2, '.', ''),
            'Currency'      => $currency,
            'ProdDesc'      => 'Sport Product Order - ' . $refNo,
            'UserName'      => $user?->fullname ?? '',
            'UserEmail'     => $user?->email ?? '',
            'UserContact'   => ($user?->calling_code ? '+' . $user->calling_code . ' ' : '') . ($user?->phone_number ?? ''),
            'Remark'        => $order->notes ?? '',
            'Lang'          => 'UTF-8',
            'Signature'     => $signature,
            'SignatureType' => 'HMACSHA512',
            'ResponseURL'   => $responseUrl,
            'BackendURL'    => $backendUrl,
        ];

        return response()->view('payment.ipay88-payment', [
            'paymentUrl'  => $gatewayUrl,
            'paymentData' => $paymentData,
            'orderNo'     => $refNo,
            'amount'      => number_format($amount, 2),
            'currency'    => $currency,
        ]);
    }

    /**
     * Mock payment page — shown when no real iPay88 credentials are configured.
     * Presents success / cancel buttons for testing.
     */
    public function mockPaymentPage($encryptedId)
    {
        $id    = Helper::decode($encryptedId);
        $order = SportProductOrder::find($id);

        if (!$order || $order->status !== SportProductOrder::STATUS_PENDING_PAYMENT) {
            abort(404, 'Order not found or no longer payable.');
        }

        return response()->view('payment.mock-payment', [
            'orderNo'     => $order->order_no,
            'amount'      => number_format((float) $order->total, 2),
            'currency'    => config('services.ipay88.currency', 'MYR'),
            'successUrl'  => url("api/v1/payment/ipay88/sport-product/{$encryptedId}/mock/success"),
            'cancelUrl'   => url("api/v1/payment/ipay88/sport-product/{$encryptedId}/mock/cancel"),
        ]);
    }

    /**
     * Mock payment success — marks the order as confirmed.
     */
    public function mockSuccess($encryptedId)
    {
        $id    = Helper::decode($encryptedId);
        $order = SportProductOrder::find($id);

        if (!$order || $order->status !== SportProductOrder::STATUS_PENDING_PAYMENT) {
            abort(404, 'Order not found or no longer payable.');
        }

        $order->update(['status' => SportProductOrder::STATUS_COMPLETED]);

        return response()->view('payment.mock-result', [
            'success'  => true,
            'orderNo'  => $order->order_no,
            'amount'   => number_format((float) $order->total, 2),
            'currency' => config('services.ipay88.currency', 'MYR'),
            'message'  => 'Payment successful (mock).',
        ]);
    }

    /**
     * Mock payment cancel — releases reserved stock and cancels the order.
     */
    public function mockCancel($encryptedId)
    {
        $id    = Helper::decode($encryptedId);
        $order = SportProductOrder::with('items')->find($id);

        if (!$order || $order->status !== SportProductOrder::STATUS_PENDING_PAYMENT) {
            abort(404, 'Order not found or no longer payable.');
        }

        foreach ($order->items as $item) {
            SportProductVariant::find($item->variant_id)?->stock()
                ->decrement('reserved_quantity', $item->quantity);
        }

        $order->update(['status' => SportProductOrder::STATUS_CANCELLED]);

        return response()->view('payment.mock-result', [
            'success'  => false,
            'orderNo'  => $order->order_no,
            'amount'   => number_format((float) $order->total, 2),
            'currency' => config('services.ipay88.currency', 'MYR'),
            'message'  => 'Payment cancelled (mock).',
        ]);
    }

    /**
     * iPay88 response URL — called by the browser after real payment.
     */
    public function paymentResponse()
    {
        // TODO: handle real iPay88 response when credentials are available
        return response()->json(['message' => 'Payment response received.']);
    }

    /**
     * iPay88 backend URL — server-to-server callback from iPay88.
     */
    public function paymentCallback()
    {
        // TODO: handle real iPay88 callback when credentials are available
        return response('OK', 200);
    }

    private function generateIPay88Signature($merchantKey, $merchantCode, $refNo, $amount, $currency)
    {
        $amount = number_format((float) $amount, 2, '.', '');
        $amount = strtr($amount, ['.' => '', ',' => '']);
        $source = $merchantKey . $merchantCode . $refNo . $amount . $currency;
        return hash_hmac('sha512', $source, $merchantKey);
    }
}
