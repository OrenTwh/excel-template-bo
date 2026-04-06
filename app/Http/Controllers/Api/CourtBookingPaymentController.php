<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourtBookingGroup;
use Helper;

class CourtBookingPaymentController extends Controller
{
    /**
     * Render the iPay88 payment page for a court booking group.
     * If no real credentials are configured, redirects to the mock payment page.
     */
    public function paymentPage($encryptedId)
    {
        $id    = Helper::decode($encryptedId);
        $group = CourtBookingGroup::with('user')->find($id);

        if (!$group || $group->status !== CourtBookingGroup::STATUS_PENDING_PAYMENT) {
            abort(404, 'Booking not found or no longer payable.');
        }

        if (!config('services.ipay88.merchant_code') || !config('services.ipay88.merchant_key')) {
            return redirect(url("api/v1/payment/ipay88/court-booking/{$encryptedId}/mock"));
        }

        $user = $group->user;

        $merchantCode = config('services.ipay88.merchant_code');
        $merchantKey  = config('services.ipay88.merchant_key');
        $gatewayUrl   = config('services.ipay88.payment_url', 'https://payment.ipay88.com.my/epayment/entry.asp');
        $responseUrl  = config('services.ipay88.response_url', url('api/v1/payment/ipay88/court-booking/response'));
        $backendUrl   = config('services.ipay88.backend_url',  url('api/v1/payment/ipay88/court-booking/callback'));
        $currency     = config('services.ipay88.currency', 'MYR');

        $refNo  = $group->group_no;
        $amount = (float) $group->total_amount;

        $signature = $this->generateIPay88Signature($merchantKey, $merchantCode, $refNo, $amount, $currency);

        $paymentData = [
            'MerchantCode'  => $merchantCode,
            'PaymentId'     => '',
            'RefNo'         => $refNo,
            'Amount'        => number_format($amount, 2, '.', ''),
            'Currency'      => $currency,
            'ProdDesc'      => 'Court Booking - ' . $refNo,
            'UserName'      => $user?->name ?? '',
            'UserEmail'     => $user?->email ?? '',
            'UserContact'   => ($user?->calling_code ? '+' . $user->calling_code . ' ' : '') . ($user?->phone_number ?? ''),
            'Remark'        => '',
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
     */
    public function mockPaymentPage($encryptedId)
    {
        $id    = Helper::decode($encryptedId);
        $group = CourtBookingGroup::find($id);

        if (!$group || $group->status !== CourtBookingGroup::STATUS_PENDING_PAYMENT) {
            abort(404, 'Booking not found or no longer payable.');
        }

        return response()->view('payment.mock-payment', [
            'orderNo'    => $group->group_no,
            'amount'     => number_format((float) $group->total_amount, 2),
            'currency'   => config('services.ipay88.currency', 'MYR'),
            'successUrl' => url("api/v1/payment/ipay88/court-booking/{$encryptedId}/mock/success"),
            'cancelUrl'  => url("api/v1/payment/ipay88/court-booking/{$encryptedId}/mock/cancel"),
        ]);
    }

    /**
     * Mock payment success — marks the group as upcoming and payment as paid.
     */
    public function mockSuccess($encryptedId)
    {
        $id    = Helper::decode($encryptedId);
        $group = CourtBookingGroup::find($id);

        if (!$group || $group->status !== CourtBookingGroup::STATUS_PENDING_PAYMENT) {
            abort(404, 'Booking not found or no longer payable.');
        }

        $group->update([
            'status'         => CourtBookingGroup::STATUS_UPCOMING,
            'payment_status' => 'paid',
            'confirmed_at'   => now(),
        ]);

        return response()->view('payment.mock-result', [
            'success'  => true,
            'orderNo'  => $group->group_no,
            'amount'   => number_format((float) $group->total_amount, 2),
            'currency' => config('services.ipay88.currency', 'MYR'),
            'message'  => 'Payment successful (mock).',
        ]);
    }

    /**
     * Mock payment cancel — marks the group as canceled.
     */
    public function mockCancel($encryptedId)
    {
        $id    = Helper::decode($encryptedId);
        $group = CourtBookingGroup::find($id);

        if (!$group || $group->status !== CourtBookingGroup::STATUS_PENDING_PAYMENT) {
            abort(404, 'Booking not found or no longer payable.');
        }

        $group->update([
            'status'       => CourtBookingGroup::STATUS_CANCELED,
            'cancelled_at' => now(),
        ]);

        return response()->view('payment.mock-result', [
            'success'  => false,
            'orderNo'  => $group->group_no,
            'amount'   => number_format((float) $group->total_amount, 2),
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
