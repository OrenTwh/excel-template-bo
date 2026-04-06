<?php

namespace App\Services;

use App\Models\PropertyPayment;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\User;
use App\Models\ApiLog;

use Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PropertyPaymentService
{
    public function __construct() {}

    public static function allPropertyPayments($request)
    {
        $query = PropertyPayment::with(['property', 'propertyUnit', 'propertyPaymentProperties.propertyUnit']);

        // Search filters
        if (!empty($request['fullname'])) {
            $query->where('fullname', 'LIKE', '%' . $request['fullname'] . '%');
        }

        if (!empty($request['email'])) {
            $query->where('email', 'LIKE', '%' . $request['email'] . '%');
        }

        if (!empty($request['phone_number'])) {
            $query->where('phone_number', 'LIKE', '%' . $request['phone_number'] . '%');
        }

        if (!empty($request['transaction_id'])) {
            $query->where('transaction_id', 'LIKE', '%' . $request['transaction_id'] . '%');
        }

        if (!empty($request['order_no'])) {
            $query->where('order_no', 'LIKE', '%' . $request['order_no'] . '%');
        }

        if (!empty($request['status'])) {
            $query->where('status', $request['status']);
        }

        if (!empty($request['transaction_type'])) {
            $query->where('transaction_type', $request['transaction_type']);
        }

        if (!empty($request['created_date'])) {
            $dates = explode(' to ', $request['created_date']);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', Carbon::parse($dates[0]))
                      ->whereDate('created_at', '<=', Carbon::parse($dates[1]));
            }
        }

        if (!empty($request->property)) {
            $query->whereHas('property', function ($q) use ($request) {
                $q->where('property_name', 'LIKE', '%' . $request->property . '%');
            });
            $filter = true;
        }

        if (!empty($request['amount_min'])) {
            $query->where('amount', '>=', $request['amount_min']);
        }

        if (!empty($request['amount_max'])) {
            $query->where('amount', '<=', $request['amount_max']);
        }

        // Pagination
        $start = $request['start'] ?? 0;
        $length = $request['length'] ?? 10;

        $totalRecords = $query->count();

        $payments = $query->offset($start)
                         ->limit($length)
                         ->orderBy('created_at', 'desc')
                         ->get();

        $payments->map(function ($payment) {
            $payment->encrypted_id = Helper::encode($payment->id);
            $payment->created_at = $payment->created_at ? $payment->created_at->format('Y-m-d H:i:s') : '-';
            $payment->updated_at = $payment->updated_at ? $payment->updated_at->format('Y-m-d H:i:s') : '-';
            $payment->formatted_amount = $payment->formatted_amount;
            return $payment;
        });

        return [
            'property_payments' => $payments,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
        ];
    }

    public static function onePropertyPayment($request)
    {
        $decodedId = Helper::decode($request->id);
        
        $payment = PropertyPayment::with(['property', 'propertyUnit'])
                                 ->find($decodedId);

        if (!$payment) {
            return null;
        }

        return $payment;
    }

    public static function createPropertyPayment($request)
    {
        $rules = [
            'property_id' => ['required', 'array', 'min:1'],
            'property_id.*' => ['required', 'integer', 'exists:properties,id'],
            'property_unit_id' => ['nullable', 'array'],
            'property_unit_id.*' => ['required', 'integer', 'exists:property_units,id'],
            'fullname' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'calling_code' => ['nullable', 'string', 'max:5'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];

        $validator = Validator::make($request->all(), $rules);

        $attributeName = [
            'property_id' => __('property_payment.property_id'),
            'property_unit_id' => __('property_payment.property_unit_id'),
            'fullname' => __('property_payment.fullname'),
            'email' => __('property_payment.email'),
            'phone_number' => __('property_payment.phone_number'),
            'calling_code' => __('property_payment.calling_code'),
            'remarks' => __('property_payment.remarks'),
            'amount' => __('property_payment.amount'),
            'currency' => __('property_payment.currency'),
            'transaction_type' => __('property_payment.transaction_type'),
            'status' => __('property_payment.status'),
        ];

        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }

        $validator->setAttributeNames($attributeName)->validate();

        // Additional validation for property units (if provided)
        if (!empty($request['property_unit_id']) && !empty($request['property_id'])) {
            $propertyUnitIds = $request['property_unit_id'];
            $propertyIds = $request['property_id'];

            $validUnits = PropertyUnit::whereIn('property_id', $propertyIds)
                ->whereIn('id', $propertyUnitIds)
                ->pluck('id')
                ->toArray();

            $invalidUnits = array_diff($propertyUnitIds, $validUnits);
            if (!empty($invalidUnits)) {
                throw new \Illuminate\Validation\ValidationException(
                    Validator::make([], []),
                    ['property_unit_id' => ['Some selected property units do not belong to the selected properties.']]
                );
            }
        }

        DB::beginTransaction();

        try {
            $createdPayments = [];
            $propertyIds = $request['property_id'];
            $propertyUnitIds = $request['property_unit_id'];

            // Create payments for each property
            foreach ($propertyIds as $propertyId) {
                $data = [
                    'user_id' => auth('user')->user()->id,
                    'property_id' => $propertyId,
                    'property_unit_id' => null, // We'll use PropertyPaymentProperty for multiple units
                    'fullname' => $request['fullname'] ?? null,
                    'email' => auth('user')->user()->email,
                    'phone_number' => $request['phone_number'] ?? null,
                    'calling_code' => $request['calling_code'] ?? '+60',
                    'remarks' => $request['remarks'] ?? null,
                    'checkout_id' => $request['checkout_id'] ?? null,
                    'checkout_url' => $request['checkout_url'] ?? null,
                    'transaction_id' => $request['transaction_id'] ?? null,
                    'layout_version' => $request['layout_version'] ?? 'v1',
                    'redirect_url' => $request['redirect_url'] ?? null,
                    'notify_url' => $request['notify_url'] ?? null,
                    'order_no' => $request['order_no'] ?? null,
                    'order_title' => $request['order_title'] ?? null,
                    'payment_attempt' => $request['payment_attempt'] ?? 1,
                    'order_detail' => $request['order_detail'] ?? null,
                    'amount' => $request['amount'],
                    'currency' => $request['currency'] ?? 'MYR',
                    'transaction_type' => $request['transaction_type'] ?? 1,
                    'status' => $request['status'] ?? 1,
                ];

                $payment = PropertyPayment::create($data);
                $createdPayments[] = $payment;

                // Create PropertyPaymentProperty entries for property units that belong to this property
                if ($propertyUnitIds && !empty($propertyUnitIds)) {
                    $propertyUnitsForThisProperty = PropertyUnit::where('property_id', $propertyId)
                        ->whereIn('id', $propertyUnitIds)
                        ->pluck('id')
                        ->toArray();

                    foreach ($propertyUnitsForThisProperty as $unitId) {
                        \App\Models\PropertyPaymentProperty::create([
                            'property_payment_id' => $payment->id,
                            'property_id' => $propertyId,
                            'property_unit_id' => $unitId,
                            'status' => 10,
                        ]);
                    }
                }
            }

            DB::commit();
            return $createdPayments;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public static function updatePropertyPayment($request)
    {
        $rules = [
            'id' => ['required'],
            'property_id' => ['required', 'exists:properties,id'],
            'property_unit_id' => ['required', 'exists:property_units,id'],
            'fullname' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'calling_code' => ['nullable', 'string', 'max:5'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'checkout_id' => ['nullable', 'string', 'max:255'],
            'checkout_url' => ['nullable', 'url', 'max:500'],
            'transaction_id' => ['nullable', 'string', 'max:255'],
            'layout_version' => ['nullable', 'string', 'max:10'],
            'redirect_url' => ['nullable', 'url', 'max:500'],
            'notify_url' => ['nullable', 'url', 'max:500'],
            'order_no' => ['nullable', 'string', 'max:255'],
            'order_title' => ['nullable', 'string', 'max:255'],
            'payment_attempt' => ['nullable', 'integer', 'min:1', 'max:10'],
            'order_detail' => ['nullable', 'string', 'max:1000'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'transaction_type' => ['nullable', 'integer', 'in:1,2'],
            'status' => ['nullable', 'integer', 'in:1,10,20'],
        ];

        $validator = Validator::make($request->all(), $rules);

        $attributeName = [
            'id' => __('property_payment.id'),
            'property_id' => __('property_payment.property_id'),
            'property_unit_id' => __('property_payment.property_unit_id'),
            'fullname' => __('property_payment.fullname'),
            'email' => __('property_payment.email'),
            'phone_number' => __('property_payment.phone_number'),
            'calling_code' => __('property_payment.calling_code'),
            'remarks' => __('property_payment.remarks'),
            'amount' => __('property_payment.amount'),
            'currency' => __('property_payment.currency'),
            'transaction_type' => __('property_payment.transaction_type'),
            'status' => __('property_payment.status'),
        ];

        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }

        $validator->setAttributeNames($attributeName)->validate();

        $decodedId = Helper::decode($request['id']);
        
        $payment = PropertyPayment::find($decodedId);

        if (!$payment) {
            return null;
        }

        $data = [
            'property_id' => $request['property_id'],
            'property_unit_id' => $request['property_unit_id'],
            'fullname' => $request['fullname'] ?? null,
            'email' => $request['email'] ?? null,
            'phone_number' => $request['phone_number'] ?? null,
            'calling_code' => $request['calling_code'] ?? '+60',
            'remarks' => $request['remarks'] ?? null,
            'checkout_id' => $request['checkout_id'] ?? null,
            'checkout_url' => $request['checkout_url'] ?? null,
            'transaction_id' => $request['transaction_id'] ?? null,
            'layout_version' => $request['layout_version'] ?? 'v1',
            'redirect_url' => $request['redirect_url'] ?? null,
            'notify_url' => $request['notify_url'] ?? null,
            'order_no' => $request['order_no'] ?? null,
            'order_title' => $request['order_title'] ?? null,
            'payment_attempt' => $request['payment_attempt'] ?? 1,
            'order_detail' => $request['order_detail'] ?? null,
            'amount' => $request['amount'],
            'currency' => $request['currency'] ?? 'MYR',
            'transaction_type' => $request['transaction_type'] ?? 1,
            'status' => $request['status'] ?? $payment->status,
        ];

        $payment->update($data);

        return $payment;
    }

    public static function updatePropertyPaymentStatus($request)
    {
        $decodedId = Helper::decode($request['id']);

        $payment = PropertyPayment::with('propertyPaymentProperties.propertyUnit')->find($decodedId);

        if (!$payment) {
            return null;
        }

        DB::beginTransaction();

        try {
            // Update payment status
            $payment->update(['status' => $request['status']]);

            // If changing to pending (status 1), release the held property units
            if ($request['status'] == 1) {
                foreach ($payment->propertyPaymentProperties as $paymentProperty) {
                    $unit = $paymentProperty->propertyUnit;

                    if ($unit) {
                        $unit->update([
                            'status' => 10, // Active
                            'unit_status' => 'available',
                        ]);
                    }
                }
            }

            DB::commit();

            return $payment;

        } catch (\Exception $e) {
            DB::rollback();
            return null;
        }
    }

    public static function markPropertyPaymentCompleted($request)
    {
        $decodedId = Helper::decode($request['id']);

        $payment = PropertyPayment::find($decodedId);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Property payment not found'
            ], 404);
        }

        // Check if payment is already completed
        if ($payment->status == 11) {
            return response()->json([
                'success' => false,
                'message' => 'Property payment is already marked as completed'
            ], 422);
        }

        // Update payment status to completed (11)
        $payment->update(['status' => 11]);

        return response()->json([
            'success' => true,
            'message' => 'Property payment marked as completed successfully'
        ]);
    }

    public static function deletePropertyPayment($request)
    {
        $decodedId = Helper::decode($request['id']);
        
        $payment = PropertyPayment::find($decodedId);

        if (!$payment) {
            return null;
        }

        $payment->delete();

        return true;
    }

    public static function getPropertyPaymentStatistics($request)
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        
        return [
            'total_payments' => PropertyPayment::count(),
            'pending_payments' => PropertyPayment::where('status', 1)->count(),
            'success_payments' => PropertyPayment::where('status', 10)->count(),
            'failed_payments' => PropertyPayment::where('status', 20)->count(),
            'today_payments' => PropertyPayment::whereDate('created_at', $today)->count(),
            'this_month_payments' => PropertyPayment::whereDate('created_at', '>=', $thisMonth)->count(),
            'total_amount' => PropertyPayment::where('status', 10)->sum('amount'),
            'this_month_amount' => PropertyPayment::where('status', 10)
                                                 ->whereDate('created_at', '>=', $thisMonth)
                                                 ->sum('amount'),
        ];
    }

    public static function getRecentPayments($limit = 10)
    {
        return PropertyPayment::with(['property', 'propertyUnit'])
                             ->orderBy('created_at', 'desc')
                             ->limit($limit)
                             ->get();
    }

    public static function getPaymentsByProperty($propertyId, $limit = 10)
    {
        return PropertyPayment::with(['property', 'propertyUnit'])
                             ->where('property_id', $propertyId)
                             ->orderBy('created_at', 'desc')
                             ->limit($limit)
                             ->get();
    }

    public static function getPaymentsByUnit($propertyUnitId, $limit = 10)
    {
        return PropertyPayment::with(['property', 'propertyUnit'])
                             ->where('property_unit_id', $propertyUnitId)
                             ->orderBy('created_at', 'desc')
                             ->limit($limit)
                             ->get();
    }

    /**
     * Helper method to get payment status label
     */
    public static function getPaymentStatusLabel($status)
    {
        switch ($status) {
            case 1:
                return 'Pending';
            case 10:
                return 'Success';
            case 20:
                return 'Failed';
            default:
                return 'Unknown';
        }
    }

    /**
     * Helper method to get transaction type label
     */
    public static function getTransactionTypeLabel($type)
    {
        switch ($type) {
            case 1:
                return 'Top Up';
            case 2:
                return 'Insurance';
            default:
                return 'Unknown';
        }
    }

    // API-specific methods

    /**
     * Create Property Payment API
     */
    public static function createPropertyPaymentApi($request)
    {
        $user = Auth::guard('user')->user();

        $validator = Validator::make($request->all(), [
            'property_unit_id' => ['required', 'array'],
            'property_unit_id.*' => ['required', 'integer', 'exists:property_units,id'],
            'fullname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'calling_code' => ['nullable', 'string', 'max:5'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $propertyUnitIds = (array) $request->property_unit_id;

        // Get only the active + available units
        $validUnits = PropertyUnit::whereIn('id', $propertyUnitIds)
            ->where('status', 10)
            ->where('unit_status', 'available')
            ->get();

        $validIds = $validUnits->pluck('id')->toArray();
        $invalidUnits = array_diff($propertyUnitIds, $validIds);

        if (!empty($invalidUnits)) {
            return response()->json([
                'success' => false,
                'message' => 'Some selected property units are not available.',
                'errors' => [
                    'property_unit_id' => [
                        'Invalid or unavailable property unit IDs: ' . implode(', ', $invalidUnits)
                    ]
                ]
            ], 422);
        }

        if ($validUnits->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid property units found.',
            ], 404);
        }


        try {
            DB::beginTransaction();

            $firstUnit = $validUnits->first();
            $property = $firstUnit->property;
            $project = $property->project;

            // Calculate totals
            $totalSellingPrice = $validUnits->sum('selling_price');
            $bookingFee = 1; //change for pg testing
            $processingFee = 0;
            $totalAmount = $bookingFee + $processingFee;

            // Generate unique order number
            $orderNo = 'PP_' . date('Ymd') . '_' . strtoupper(Str::random(6));

            // Create the payment record
            $propertyPayment = PropertyPayment::create([
                'user_id' => $user->id,
                'property_id' => $property->id,
                'property_unit_id' => null,
                'fullname' => $request->fullname ? $request->fullname : $user->fullname,
                'email' => $request->email ? $request->email : $user->email,
                'phone_number' => $request->phone_number ? $request->phone_number : $user->phone_number,
                'calling_code' => $request->calling_code ? $request->calling_code : $user->calling_code,
                'remarks' => $request->remarks,
                'order_no' => $orderNo,
                'order_title' => 'Property Booking Payment',
                'order_detail' => 'Booking fee for ' . $validUnits->count() . ' unit(s)',
                'amount' => $totalAmount,
                'currency' => 'MYR',
                'transaction_type' => 1, // 1=Top Up, 2=Insurance
                'status' => 1, // Pending
            ]);

            // Create PropertyPaymentProperty entries for each unit and update unit status
            foreach ($validUnits as $unit) {
                \App\Models\PropertyPaymentProperty::create([
                    'property_payment_id' => $propertyPayment->id,
                    'property_id' => $property->id,
                    'property_unit_id' => $unit->id,
                    'status' => 10,
                ]);

                // Update unit status to reserved (12) and unit_status to 'reserved'
                $unit->update([
                    'status' => 12,
                    'unit_status' => 'reserved',
                ]);
            }

            // Build unit details (same format as preview)
            $unitDetails = $validUnits->map(function ($unit) {
                return [
                    'unit_number'    => $unit->unit_number,
                    'unit_name'      => $unit->unit_name,
                    'unit_type'      => $unit->unit_type,
                    'unit_status'    => $unit->unit_status,
                    'floor'          => $unit->floor,
                    'price'          => (string) Helper::numberFormatV2($unit->selling_price, 2, true),
                    'selling_price'  => (string) Helper::numberFormatV2($unit->selling_price, 2, true),
                    'rental_price'   => $unit->rental_price ? (string) Helper::numberFormatV2($unit->rental_price, 2, true) : null,
                    'property_name'  => $unit->property->decoded_translations->en->property_name ?? null,
                    'property_details' => [
                        'bedrooms'         => $unit->bedrooms,
                        'bathrooms'        => $unit->bathrooms,
                        'balcony'          => $unit->balcony,
                        'storeroom'        => $unit->storeroom,
                        'parking_spaces'   => $unit->parking_spaces,
                        'built_up_area'    => $unit->built_up_area,
                        'maintenance_fee'  => Helper::numberFormatV2($unit->maintenance_fee, 2, true),
                    ]
                ];
            });

            // Initiate iPay88 Payment
            $merchantCode = config('services.ipay88.merchant_code');
            $merchantKey = config('services.ipay88.merchant_key');
            $paymentUrl = config('services.ipay88.payment_url');
            $responseUrl = config('services.ipay88.response_url');
            $backendUrl = config('services.ipay88.backend_url');
            $currency = config('services.ipay88.currency', 'MYR');

            // Prepare payment data
            $refNo = $propertyPayment->order_no;
            $amount = $propertyPayment->amount;
            $prodDesc = $propertyPayment->order_title ?? 'Property Booking Payment';
            $userName = $propertyPayment->fullname;
            $userEmail = $propertyPayment->email;
            $userContact = ($propertyPayment->calling_code ?? '') . ($propertyPayment->phone_number ?? '');
            $remark = $propertyPayment->remarks ?? '';
            $lang = 'UTF-8';

            // Generate signature
            $signature = self::generateIPay88Signature($merchantKey, $merchantCode, $refNo, $amount, $currency);

            // Prepare iPay88 form data
            $paymentData = [
                'MerchantCode' => $merchantCode,
                'PaymentId' => '', // Leave empty for payment method selection page
                'RefNo' => $refNo,
                'Amount' => number_format((float)$amount, 2, '.', ''),
                'Currency' => $currency,
                'ProdDesc' => $prodDesc,
                'UserName' => $userName,
                'UserEmail' => $userEmail,
                'UserContact' => $userContact,
                'Remark' => $remark,
                'Lang' => $lang,
                'Signature' => $signature,
                'ResponseURL' => $responseUrl,
                'BackendURL' => $backendUrl,
            ];

            // Update payment with iPay88 details
            $propertyPayment->update([
                'payment_gateway' => 'ipay88',
                'payment_gateway_ref' => $refNo,
            ]);

            $data = [
                'id' => $propertyPayment->id,
                'order_no' => $propertyPayment->order_no,
                'payment_url' => $paymentUrl,
                'payment_data' => $paymentData,
                'project_details' => $project ? [
                    'id'           => $project->id,
                    'title'        => $project->decoded_translations->en->title ?? null,
                    'translations' => $project->decoded_translations ?? null,
                    'project_details' => $project->decoded_project_details,
                    'property_type_label' => ProjectService::getPropertyTypeLabel($project->property_type),
                    'project_type_label' => ProjectService::getPropertyTypeLabel($project->property_type),
                    'logo'         => $project->logo_path ? url($project->logo_path) : null,
                    'logo_path'    => $project->logo_path ? url($project->logo_path) : null,
                    'property_type' => $project->property_type ?? null,
                    'tenure'        => $project->tenure ?? null,
                    'location' => [
                        'address_line_1' => $project->address_line_1,
                        'address_line_2' => $project->address_line_2,
                        'address_line_3' => $project->address_line_3,
                        'state' => $project->state,
                        'postcode' => $project->postcode,
                        'latitude' => $project->latitude,
                        'longitude' => $project->longitude,
                        'locations' => $project->project_locations_details,
                    ],
                    'amenities_list'=> $project->amenities_details ?? [],
                ] : null,
                'property_details' => [
                    'id'               => $property->id,
                    'property_name'    => $property->decoded_translations->en->property_name ?? null,
                    'translations'     => $property->decoded_translations ?? null,
                    'thumbnail'        => $property->thumbnail ? url($property->thumbnail) : null,
                    'property_preview' => $property->preview ? url($property->preview) : null,
                    'property_type'    => $property->property_type,
                    'property_status'  => $property->property_status,
                    'tenure'           => $property->tenure,
                    'building_type'    => $property->building_type,
                    'furnishing_status'=> $property->furnishing_status,
                    'bedrooms'         => $property->bedrooms,
                    'bathrooms'        => $property->bathrooms,
                    'build_up_area_psf'=> Helper::numberFormatV2($property->build_up_area_psf, 2, true),
                    'selling_price_psf'=> Helper::numberFormatV2($property->selling_price_psf, 2, true),
                    'selling_price_unit'=> Helper::numberFormatV2($property->selling_price_unit, 2, true),
                    'maintenance_fee_psf'=> Helper::numberFormatV2($property->maintenance_fee_psf, 2, true),
                    'completion_date'  => $property->completion_date,
                ],
                'unit_details' => $unitDetails,
                'payment_details' => [
                    'booking_fee'    => (string) Helper::numberFormatV2($bookingFee, 2, true),
                    'processing_fee' => (string) Helper::numberFormatV2($processingFee, 2, true),
                    'total_amount'   => (string) Helper::numberFormatV2($totalAmount, 2, true),
                    'currency'       => 'MYR',
                ]
            ];

            DB::commit();

            // Generate redirect URL for mobile app webview
            $redirectUrl = url('api/v1/payment/ipay88/' . $propertyPayment->id);

            // Replace payment_url with redirect_url
            $data['payment_url'] = $redirectUrl;

            return response()->json([
                'success' => true,
                'message' => 'Property payment created successfully for ' . $validUnits->count() . ' units',
                'data' => $data
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create property payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview Property Payment API
     */
    public static function previewPropertyPaymentApi($request)
    {
        $validator = Validator::make($request->all(), [
            'property_unit_id' => ['required', 'array'],
            'property_unit_id.*' => ['required', 'integer', 'exists:property_units,id'],
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
    
        $propertyUnitIds = (array) $request->property_unit_id;
    
        \Log::info('Preview Payment - Requested Unit IDs:', ['ids' => $propertyUnitIds]);
    
        // Get only the active + available units
        $validUnits = PropertyUnit::whereIn('id', $propertyUnitIds)
            ->where('status', 10)
            ->where('unit_status', 'available')
            ->get();
    
        $validIds = $validUnits->pluck('id')->toArray();
        $invalidUnits = array_diff($propertyUnitIds, $validIds);
    
        if (!empty($invalidUnits)) {
            return response()->json([
                'success' => false,
                'message' => 'Some selected property units are not available.',
                'errors' => [
                    'property_unit_id' => [
                        'Invalid or unavailable property unit IDs: ' . implode(', ', $invalidUnits)
                    ]
                ]
            ], 422);
        }
    
        if ($validUnits->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid property units found.',
            ], 404);
        }
    
        $firstUnit = $validUnits->first();
        $property = $firstUnit->property;
        $project = $property->project;
    
        // Calculate totals
        $totalSellingPrice = $validUnits->sum('selling_price');
        $bookingFee = 50000;
        $processingFee = 0;
        $totalAmount = $bookingFee + $processingFee;
    
        // Build unit details
        $unitDetails = $validUnits->map(function ($unit) {
            return [
                'unit_number'    => $unit->unit_number,
                'unit_name'      => $unit->unit_name,
                'unit_type'      => $unit->unit_type,
                'unit_status'    => $unit->unit_status,
                'floor'          => $unit->floor,
                'price'          => (string) Helper::numberFormatV2($unit->selling_price,2,true),
                'selling_price'  => (string) Helper::numberFormatV2($unit->selling_price,2,true),
                'rental_price'   => $unit->rental_price ? (string) Helper::numberFormatV2($unit->rental_price,2,true) : null,
                'property_name'  => $unit->property->decoded_translations->en->property_name ?? null,
                'property_details' => [
                    'bedrooms'         => $unit->bedrooms,
                    'bathrooms'        => $unit->bathrooms,
                    'balcony'          => $unit->balcony,
                    'storeroom'        => $unit->storeroom,
                    'parking_spaces'   => $unit->parking_spaces,
                    'built_up_area'    => $unit->built_up_area,
                    'maintenance_fee'  => Helper::numberFormatV2($unit->maintenance_fee,2,true),
                ]
            ];
        });
    
        $data = [
            'id' => null, // booking ID not created yet (preview only)
            'project_details' => $project ? [
                'id'           => $project->id,
                'title'        => $project->decoded_translations->en->title ?? null,
                'translations' => $project->decoded_translations ?? null,
                'project_details' => $project->decoded_project_details,
                'property_type_label' => ProjectService::getPropertyTypeLabel($project->property_type),
                'project_type_label' => ProjectService::getPropertyTypeLabel($project->property_type),
                'logo'         => $project->logo_path ? url($project->logo_path) : null,
                'logo_path'    => $project->logo_path ? url($project->logo_path) : null,
                'property_type' => $project->property_type ?? null,
                'tenure'        => $project->tenure ?? null,
                'location' => [
                    'address_line_1' => $project->address_line_1,
                    'address_line_2' => $project->address_line_2,
                    'address_line_3' => $project->address_line_3,
                    'state' => $project->state,
                    'postcode' => $project->postcode,
                    'latitude' => $project->latitude,
                    'longitude' => $project->longitude,
                    'locations' => $project->project_locations_details,
                ],
                'amenities_list'=> $project->amenities_details ?? [],
            ] : null,
            'property_details' => [
                'id'               => $property->id,
                'property_name'    => $property->decoded_translations->en->property_name ?? null,
                'translations'     => $property->decoded_translations ?? null,
                'thumbnail'        => $property->thumbnail ? url($property->thumbnail) : null,
                'property_preview' => $property->preview ? url($property->preview) : null,
                'property_type'    => $property->property_type,
                'property_status'  => $property->property_status,
                'tenure'           => $property->tenure,
                'building_type'    => $property->building_type,
                'furnishing_status'=> $property->furnishing_status,
                'bedrooms'         => $property->bedrooms,
                'bathrooms'        => $property->bathrooms,
                'build_up_area_psf'=> Helper::numberFormatV2($property->build_up_area_psf,2,true),
                'selling_price_psf'=> Helper::numberFormatV2($property->selling_price_psf,2,true),
                'selling_price_unit'=> Helper::numberFormatV2($property->selling_price_unit,2,true),
                'maintenance_fee_psf'=> Helper::numberFormatV2($property->maintenance_fee_psf,2,true),
                'completion_date'  => $property->completion_date,
            ],
            'unit_details' => $unitDetails,
            'payment_details' => [
                'booking_fee'    => (string) Helper::numberFormatV2($bookingFee,2,true),
                'processing_fee' => (string) Helper::numberFormatV2($processingFee,2,true),
                'total_amount'   => (string) Helper::numberFormatV2($totalAmount,2,true),
                'currency'       => 'MYR',
            ]
        ];
    
        return response()->json([
            'success' => true,
            'message' => 'Payment preview generated successfully for ' . $validUnits->count() . ' units',
            'data' => $data
        ]);
    }

    /**
     * Get User Property Payments API
     */
    public static function getUserPropertyPaymentsApi($request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|integer|in:1,10,11,20',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = auth()->user();

            $query = PropertyPayment::with(['property.project', 'propertyPaymentProperties.propertyUnit'])
                ->where('user_id', $user->id);

            // Filter by status if provided
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Order by creation date (newest first)
            $query->orderBy('created_at', 'DESC');

            // Pagination
            $perPage = $request->input('per_page', 10);
            $perPage = min($perPage, 50);

            $propertyPayments = $query->paginate($perPage);

            // Transform the data for API response
            $propertyPayments->getCollection()->transform(function ($payment) {
                return self::formatPropertyPaymentForApi($payment);
            });

            return response()->json([
                'success' => true,
                'data' => $propertyPayments->items(),
                'pagination' => [
                    'current_page' => $propertyPayments->currentPage(),
                    'last_page' => $propertyPayments->lastPage(),
                    'per_page' => $propertyPayments->perPage(),
                    'total' => $propertyPayments->total(),
                    'from' => $propertyPayments->firstItem(),
                    'to' => $propertyPayments->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching property payments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Property Payment API
     */
    public static function getPropertyPaymentApi($request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:property_payments,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Property payment not found',
                'errors' => $validator->errors()
            ], 404);
        }

        try {
            $user = auth()->user();

            $propertyPayment = PropertyPayment::with(['property.project', 'propertyPaymentProperties.propertyUnit'])
                ->where('id', $request->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$propertyPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property payment not found or access denied'
                ], 404);
            }

            $paymentData = self::formatPropertyPaymentForApi($propertyPayment);

            return response()->json([
                'success' => true,
                'data' => $paymentData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching property payment details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Property Payment API
     */
    public static function updatePropertyPaymentApi($request)
    {
        $user = Auth::guard('user')->user();

        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer', 'exists:property_payments,id'],
            'fullname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'calling_code' => ['nullable', 'string', 'max:5'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'order_title' => ['nullable', 'string', 'max:255'],
            'order_detail' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $propertyPayment = PropertyPayment::where('id', $request->id)
                ->where('email', $user->email) // Ensure user can only update their own payments
                ->first();

            if (!$propertyPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property payment not found or access denied',
                ], 404);
            }

            // Only allow updates for pending payments
            if ($propertyPayment->status != 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending payments can be updated',
                ], 422);
            }

            $propertyPayment->update([
                'fullname' => $request->fullname,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'calling_code' => $request->calling_code ?? $propertyPayment->calling_code,
                'remarks' => $request->remarks,
                'amount' => $request->amount,
                'order_title' => $request->order_title ?? $propertyPayment->order_title,
                'order_detail' => $request->order_detail,
            ]);

            // Load relationships
            $propertyPayment->load(['property', 'propertyUnit']);

            return response()->json([
                'success' => true,
                'message' => 'Property payment updated successfully',
                'data' => [
                    'property_payment' => [
                        'id' => $propertyPayment->id,
                        'encrypted_id' => $propertyPayment->encrypted_id,
                        'order_no' => $propertyPayment->order_no,
                        'amount' => $propertyPayment->formatted_amount,
                        'currency' => $propertyPayment->currency,
                        'status' => $propertyPayment->status_label,
                        'updated_at' => $propertyPayment->updated_at->format('Y-m-d H:i:s'),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update property payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel Property Payment API
     */
    public static function cancelPropertyPaymentApi($request)
    {
        $user = Auth::guard('user')->user();

        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer', 'exists:property_payments,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $propertyPayment = PropertyPayment::where('id', $request->id)
                ->where('email', $user->email) // Ensure user can only cancel their own payments
                ->first();

            if (!$propertyPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property payment not found or access denied',
                ], 404);
            }

            // Only allow cancellation for pending payments
            if ($propertyPayment->status != 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending payments can be cancelled',
                ], 422);
            }

            $propertyPayment->update(['status' => 20]); // Failed/Cancelled

            return response()->json([
                'success' => true,
                'message' => 'Property payment cancelled successfully',
                'data' => [
                    'property_payment' => [
                        'id' => $propertyPayment->id,
                        'encrypted_id' => $propertyPayment->encrypted_id,
                        'order_no' => $propertyPayment->order_no,
                        'status' => $propertyPayment->status_label,
                        'updated_at' => $propertyPayment->updated_at->format('Y-m-d H:i:s'),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel property payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Payment Status Options API
     */
    public static function getPaymentStatusOptionsApi($request)
    {
        try {
            $statusOptions = PropertyPayment::statusOptions();
            $formattedOptions = collect($statusOptions)->map(function ($label, $value) {
                return [
                    'value' => $value,
                    'label' => $label,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'message' => 'Payment status options retrieved successfully',
                'data' => [
                    'status_options' => $formattedOptions
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment status options',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Transaction Type Options API
     */
    public static function getTransactionTypeOptionsApi($request)
    {
        try {
            $transactionTypeOptions = PropertyPayment::transactionTypeOptions();
            $formattedOptions = collect($transactionTypeOptions)->map(function ($label, $value) {
                return [
                    'value' => $value,
                    'label' => $label,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'message' => 'Transaction type options retrieved successfully',
                'data' => [
                    'transaction_type_options' => $formattedOptions
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve transaction type options',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process Payment Callback API
     */
    public static function processPaymentCallbackApi($request)
    {
        $validator = Validator::make($request->all(), [
            'checkout_id' => ['required', 'string', 'max:255'],
            'transaction_id' => ['required', 'string', 'max:255'],
            'status' => ['required', 'integer', 'in:1,10,20'],
            'order_no' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $propertyPayment = PropertyPayment::where('order_no', $request->order_no)->first();

            if (!$propertyPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property payment not found for the given order number',
                ], 404);
            }

            $propertyPayment->update([
                'checkout_id' => $request->checkout_id,
                'transaction_id' => $request->transaction_id,
                'status' => $request->status,
                'payment_attempt' => ($propertyPayment->payment_attempt ?? 0) + 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment status updated successfully',
                'data' => [
                    'property_payment' => [
                        'id' => $propertyPayment->id,
                        'order_no' => $propertyPayment->order_no,
                        'status' => $propertyPayment->status_label,
                        'transaction_id' => $propertyPayment->transaction_id,
                        'updated_at' => $propertyPayment->updated_at->format('Y-m-d H:i:s'),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment callback',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get available property units for payments
     */
    public static function getAvailableUnitsApi($request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'unit_type' => ['nullable', 'integer', 'in:1,2,3'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'bedrooms' => ['nullable', 'integer', 'min:1', 'max:99'],
            'bathrooms' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $property = Property::where('id', $request->property_id)
                ->where('status', 10)
                ->first();

            if (!$property) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property not found or inactive'
                ], 404);
            }

            // Get property units that are active (status 10) and available for booking/payment
            $query = PropertyUnit::with('property:id,property_name')
                ->where('property_id', $request->property_id)
                ->where('status', 10)
                ->where('unit_status', 'available'); // Only available units

            // Apply filters
            if ($request->filled('unit_type')) {
                $query->where('unit_type', $request->unit_type);
            }

            if ($request->filled('min_price')) {
                $query->where('selling_price', '>=', $request->min_price);
            }

            if ($request->filled('max_price')) {
                $query->where('selling_price', '<=', $request->max_price);
            }

            if ($request->filled('bedrooms')) {
                $query->where('bedrooms', $request->bedrooms);
            }

            if ($request->filled('bathrooms')) {
                $query->where('bathrooms', $request->bathrooms);
            }

            // Exclude units that have active bookings (status 10) for today or future dates
            $currentDate = Carbon::now()->format('Y-m-d');
            $bookedUnitIds = \App\Models\Booking::where('property_id', $request->property_id)
                ->where('status', 10) // Active bookings
                ->whereDate('booking_date', '>=', $currentDate) // Today or future
                ->whereNotNull('property_unit_id')
                ->pluck('property_unit_id')
                ->unique()
                ->toArray();

            if (!empty($bookedUnitIds)) {
                $query->whereNotIn('id', $bookedUnitIds);
            }

            $availableUnits = $query->orderBy('floor', 'asc')
                ->orderBy('unit_number', 'asc')
                ->get()
                ->map(function ($unit) {
                    return [
                        'id' => $unit->id,
                        'property_id' => $unit->property_id,
                        'property_name' => $unit->property->property_name ?? null,
                        'unit_number' => $unit->unit_number,
                        'unit_name' => $unit->unit_name,
                        'floor' => $unit->floor,
                        'bedrooms' => $unit->bedrooms,
                        'bathrooms' => $unit->bathrooms,
                        'balcony' => $unit->balcony,
                        'storeroom' => $unit->storeroom,
                        'parking_spaces' => $unit->parking_spaces,
                        'built_up_area' => $unit->built_up_area,
                        'selling_price' => $unit->selling_price,
                        'rental_price' => $unit->rental_price,
                        'rental_price_per_night' => $unit->rental_price_per_night,
                        'maintenance_fee' => $unit->maintenance_fee,
                        'unit_status' => $unit->unit_status,
                        'unit_status_label' => $unit->unit_status_label,
                        'unit_type' => $unit->unit_type,
                        'unit_type_label' => $unit->unit_type_label,
                        'availability_date' => $unit->availability_date,
                        'remarks' => $unit->remarks,
                        'created_at' => $unit->created_at,
                        'updated_at' => $unit->updated_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Available property units retrieved successfully',
                'data' => $availableUnits,
                'meta' => [
                    'property_id' => $request->property_id,
                    'property_name' => $property->property_name,
                    'total_available_units' => $availableUnits->count(),
                    'filters_applied' => [
                        'unit_type' => $request->unit_type,
                        'min_price' => $request->min_price,
                        'max_price' => $request->max_price,
                        'bedrooms' => $request->bedrooms,
                        'bathrooms' => $request->bathrooms,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching available units',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // iPay88 Payment Gateway Integration
    // ============================================

    /**
     * Generate iPay88 signature for payment request
     */
    private static function generateIPay88Signature($merchantKey, $merchantCode, $refNo, $amount, $currency)
    {
        // iPay88 Signature: SHA256(MerchantKey + MerchantCode + RefNo + Amount + Currency)
        // Amount must be formatted as 2 decimal places, then strip dots and commas
        $amount = number_format((float)$amount, 2, '.', '');
        $amount = strtr($amount, array('.' => '', ',' => ''));
        $source = $merchantKey . $merchantCode . $refNo . $amount . $currency;
        return hash('sha256', $source);
    }

    /**
     * Verify iPay88 response signature
     */
    private static function verifyIPay88Signature($merchantKey, $merchantCode, $paymentId, $refNo, $amount, $currency, $status, $signature)
    {
        // iPay88 Response Signature: SHA256(MerchantKey + MerchantCode + PaymentId + RefNo + Amount + Currency + Status)
        // Amount must be formatted as 2 decimal places, then strip dots and commas
        $amount = number_format((float)$amount, 2, '.', '');
        $amount = strtr($amount, array('.' => '', ',' => ''));
        $source = $merchantKey . $merchantCode . $paymentId . $refNo . $amount . $currency . $status;
        $expectedSignature = hash('sha256', $source);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Redirect to iPay88 Payment Page (HTML Form Auto-Submit)
     */
    public static function redirectToIPay88Payment($request)
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => ['required', 'integer', 'exists:property_payments,id'],
            'payment_method' => ['nullable', 'integer'], // iPay88 payment method code (optional)
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::guard('user')->user();

            $propertyPayment = PropertyPayment::with(['property', 'propertyPaymentProperties.propertyUnit'])
                ->where('id', $request->payment_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$propertyPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property payment not found or access denied'
                ], 404);
            }

            // Only allow initiation for pending payments
            if ($propertyPayment->status != 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending payments can be initiated'
                ], 422);
            }

            // Get iPay88 configuration
            $merchantCode = config('services.ipay88.merchant_code');
            $merchantKey = config('services.ipay88.merchant_key');
            $paymentUrl = config('services.ipay88.payment_url');
            $responseUrl = config('services.ipay88.response_url');
            $backendUrl = config('services.ipay88.backend_url');
            $currency = config('services.ipay88.currency', 'MYR');

            // Prepare payment data
            $refNo = $propertyPayment->order_no;
            $amount = $propertyPayment->amount;
            $prodDesc = $propertyPayment->order_title ?? 'Property Payment';
            $userName = $propertyPayment->fullname;
            $userEmail = $propertyPayment->email;
            $userContact = ($propertyPayment->calling_code ?? '') . ($propertyPayment->phone_number ?? '');
            $remark = $propertyPayment->remarks ?? '';
            $lang = 'UTF-8';

            // Generate signature
            $signature = self::generateIPay88Signature($merchantKey, $merchantCode, $refNo, $amount, $currency);

            // Prepare iPay88 form data
            $paymentData = [
                'MerchantCode' => $merchantCode,
                'PaymentId' => $request->payment_method ?? '', // Payment method code (leave empty for selection page)
                'RefNo' => $refNo,
                'Amount' => number_format((float)$amount, 2, '.', ''),
                'Currency' => $currency,
                'ProdDesc' => $prodDesc,
                'UserName' => $userName,
                'UserEmail' => $userEmail,
                'UserContact' => $userContact,
                'Remark' => $remark,
                'Lang' => $lang,
                'Signature' => $signature,
                'ResponseURL' => $responseUrl,
                'BackendURL' => $backendUrl,
            ];

            // Update payment with iPay88 details
            $propertyPayment->update([
                'payment_gateway' => 'ipay88',
                'payment_gateway_ref' => $refNo,
            ]);

            // Generate HTML form that auto-submits to iPay88
            $html = view('payment.ipay88-redirect', [
                'paymentUrl' => $paymentUrl,
                'paymentData' => $paymentData,
                'orderNo' => $refNo,
            ])->render();

            return response($html);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate iPay88 payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initiate iPay88 Payment
     */
    public static function initiateIPay88PaymentApi($request)
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => ['required', 'integer', 'exists:property_payments,id'],
            'payment_method' => ['nullable', 'integer'], // iPay88 payment method code (optional)
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::guard('user')->user();

            $propertyPayment = PropertyPayment::with(['property', 'propertyPaymentProperties.propertyUnit'])
                ->where('id', $request->payment_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$propertyPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property payment not found or access denied'
                ], 404);
            }

            // Only allow initiation for pending payments
            if ($propertyPayment->status != 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending payments can be initiated'
                ], 422);
            }

            // Get iPay88 configuration
            $merchantCode = config('services.ipay88.merchant_code');
            $merchantKey = config('services.ipay88.merchant_key');
            $paymentUrl = config('services.ipay88.payment_url');
            $responseUrl = config('services.ipay88.response_url');
            $backendUrl = config('services.ipay88.backend_url');
            $currency = config('services.ipay88.currency', 'MYR');

            // Prepare payment data
            $refNo = $propertyPayment->order_no;
            $amount = $propertyPayment->amount;
            $prodDesc = $propertyPayment->order_title ?? 'Property Payment';
            $userName = $propertyPayment->fullname;
            $userEmail = $propertyPayment->email;
            $userContact = ($propertyPayment->calling_code ?? '') . ($propertyPayment->phone_number ?? '');
            $remark = $propertyPayment->remarks ?? '';
            $lang = 'UTF-8'; // or 'ISO-8859-1'

            // Generate signature
            $signature = self::generateIPay88Signature($merchantKey, $merchantCode, $refNo, $amount, $currency);

            // Prepare iPay88 form data
            $paymentData = [
                'MerchantCode' => $merchantCode,
                'PaymentId' => $request->payment_method ?? '', // Payment method code (leave empty for selection page)
                'RefNo' => $refNo,
                'Amount' => number_format((float)$amount, 2, '.', ''),
                'Currency' => $currency,
                'ProdDesc' => $prodDesc,
                'UserName' => $userName,
                'UserEmail' => $userEmail,
                'UserContact' => $userContact,
                'Remark' => $remark,
                'Lang' => $lang,
                'Signature' => $signature,
                'ResponseURL' => $responseUrl,
                'BackendURL' => $backendUrl,
            ];

            // Update payment with iPay88 details
            $propertyPayment->update([
                'payment_gateway' => 'ipay88',
                'payment_gateway_ref' => $refNo,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'iPay88 payment initiated successfully',
                'data' => [
                    'payment_url' => $paymentUrl,
                    'payment_data' => $paymentData,
                    'payment_id' => $propertyPayment->id,
                    'order_no' => $refNo,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate iPay88 payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle iPay88 Response (User Redirect)
     */
    public static function handleIPay88ResponseApi($request)
    {

        $url = $request->fullUrl();
        $baseUrl = parse_url($url, PHP_URL_SCHEME) . "://" . parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH);

        ApiLog::create( [
            'url' => $baseUrl,
            'method' => $request->method(),
            'raw_response' => json_encode( $request->all() ),
        ] );

        try {
            // Get iPay88 response parameters
            $merchantCode = $request->input('MerchantCode');
            $paymentId = $request->input('PaymentId');
            $refNo = $request->input('RefNo');
            $amount = $request->input('Amount');
            $currency = $request->input('Currency');
            $remark = $request->input('Remark');
            $transId = $request->input('TransId');
            $authCode = $request->input('AuthCode');
            $status = $request->input('Status');
            $errDesc = $request->input('ErrDesc');
            $signature = $request->input('Signature');

            // Get merchant key from config
            $merchantKey = config('services.ipay88.merchant_key');

            // Verify signature
            $isValidSignature = self::verifyIPay88Signature(
                $merchantKey,
                $merchantCode,
                $paymentId,
                $refNo,
                $amount,
                $currency,
                $status,
                $signature
            );

            if (!$isValidSignature) {
                \Log::error('iPay88 Response: Invalid signature', [
                    'refNo' => $refNo,
                    'signature' => $signature
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature'
                ], 400);
            }

            // Find payment by order number
            $propertyPayment = PropertyPayment::where('order_no', $refNo)->first();

            if (!$propertyPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            // Update payment status based on iPay88 response
            // Status: 1 = Success, 0 = Failed
            $paymentStatus = ($status == '1') ? 10 : 20; // 10 = Success, 20 = Failed

            $propertyPayment->update([
                'transaction_id' => $transId,
                'status' => $paymentStatus,
                'payment_gateway_response' => json_encode([
                    'payment_id' => $paymentId,
                    'trans_id' => $transId,
                    'auth_code' => $authCode,
                    'status' => $status,
                    'err_desc' => $errDesc,
                    'remark' => $remark,
                    'response_time' => now()->toDateTimeString(),
                ]),
                'payment_attempt' => ($propertyPayment->payment_attempt ?? 0) + 1,
            ]);

            // If payment is successful, update unit status to sold/booked
            if ($paymentStatus == 10) {
                $propertyPaymentProperties = \App\Models\PropertyPaymentProperty::where('property_payment_id', $propertyPayment->id)->get();

                foreach ($propertyPaymentProperties as $paymentProperty) {
                    $unit = PropertyUnit::find($paymentProperty->property_unit_id);
                    if ($unit) {
                        $unit->update([
                            'status' => 11, // Sold/Booked
                            'unit_status' => 'sold',
                        ]);
                    }
                }
            }

            // return response()->json([
            //     'success' => ($paymentStatus == 10) ? true : false,
            //     'message' => ($paymentStatus == 10) ? 'Payment successful' : 'Payment failed',
            //     'data' => [
            //         'order_no' => $refNo,
            //         'transaction_id' => $transId,
            //         'status' => $paymentStatus,
            //         'status_label' => self::getPaymentStatusLabel($paymentStatus),
            //         'auth_code' => $authCode,
            //         'err_desc' => $errDesc,
            //     ]
            // ]);
            
            $success = ( $paymentStatus == 10 ) ? 'true' : 'false';
            $callbackUrl = "xpark://ipay88/callback?success={$success}";
            
            return response()->make( "
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Redirecting...</title>

                    <meta http-equiv='refresh' content='0;url={$callbackUrl}'>

                    <script>
                        const response = " . json_encode( [
                            'success' => ( $paymentStatus == 10 ) ? true : false,
                            'message' => ( $paymentStatus == 10 ) ? 'Payment successful' : 'Payment failed',
                            'data'    => [
                                'order_no'        => $refNo,
                                'transaction_id'  => $transId,
                                'status'          => $paymentStatus,
                                'status_label'    => self::getPaymentStatusLabel( $paymentStatus ),
                                'auth_code'       => $authCode,
                                'err_desc'        => $errDesc,
                            ]
                        ] ) . ";

                        console.log( response );

                        // JS Redirect using dynamic URL
                        window.location.href = '{$callbackUrl}';
                    </script>

                </head>
                <body>
                    Redirecting...
                </body>
                </html>
            ", 200, [ 'Content-Type' => 'text/html' ] );

        } catch (\Exception $e) {
            \Log::error('iPay88 Response Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to process iPay88 response',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle iPay88 Backend Notification
     */
    public static function handleIPay88BackendApi($request)
    {
        
        $url = $request->fullUrl();
        $baseUrl = parse_url($url, PHP_URL_SCHEME) . "://" . parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH);

        ApiLog::create( [
            'url' => $baseUrl,
            'method' => $request->method(),
            'raw_response' => json_encode( $request->all() ),
        ] );

        try {
            // Get iPay88 backend parameters
            $merchantCode = $request->input('MerchantCode');
            $paymentId = $request->input('PaymentId');
            $refNo = $request->input('RefNo');
            $amount = $request->input('Amount');
            $currency = $request->input('Currency');
            $remark = $request->input('Remark');
            $transId = $request->input('TransId');
            $authCode = $request->input('AuthCode');
            $status = $request->input('Status');
            $errDesc = $request->input('ErrDesc');
            $signature = $request->input('Signature');

            \Log::info('iPay88 Backend Notification', [
                'refNo' => $refNo,
                'status' => $status,
                'transId' => $transId,
            ]);

            // Get merchant key from config
            $merchantKey = config('services.ipay88.merchant_key');

            // Verify signature
            $isValidSignature = self::verifyIPay88Signature(
                $merchantKey,
                $merchantCode,
                $paymentId,
                $refNo,
                $amount,
                $currency,
                $status,
                $signature
            );

            if (!$isValidSignature) {
                \Log::error('iPay88 Backend: Invalid signature', [
                    'refNo' => $refNo,
                    'signature' => $signature
                ]);

                // iPay88 expects "00" response for successful receipt
                return response('INVALID_SIGNATURE', 400);
            }

            // Find payment by order number
            $propertyPayment = PropertyPayment::where('order_no', $refNo)->first();

            if (!$propertyPayment) {
                \Log::error('iPay88 Backend: Payment not found', ['refNo' => $refNo]);
                return response('PAYMENT_NOT_FOUND', 404);
            }

            // Update payment status based on iPay88 response
            // Status: 1 = Success, 0 = Failed
            $paymentStatus = ($status == '1') ? 10 : 20; // 10 = Success, 20 = Failed

            $propertyPayment->update([
                'transaction_id' => $transId,
                'status' => $paymentStatus,
                'payment_gateway_response' => json_encode([
                    'payment_id' => $paymentId,
                    'trans_id' => $transId,
                    'auth_code' => $authCode,
                    'status' => $status,
                    'err_desc' => $errDesc,
                    'remark' => $remark,
                    'backend_time' => now()->toDateTimeString(),
                ]),
                'payment_attempt' => ($propertyPayment->payment_attempt ?? 0) + 1,
            ]);

            // If payment is successful, update unit status to sold/booked
            if ($paymentStatus == 10) {
                $propertyPaymentProperties = \App\Models\PropertyPaymentProperty::where('property_payment_id', $propertyPayment->id)->get();

                foreach ($propertyPaymentProperties as $paymentProperty) {
                    $unit = PropertyUnit::find($paymentProperty->property_unit_id);
                    if ($unit) {
                        $unit->update([
                            'status' => 11, // Sold/Booked
                            'unit_status' => 'sold',
                        ]);
                    }
                }
            }

            \Log::info('iPay88 Backend: Payment updated successfully', [
                'refNo' => $refNo,
                'status' => $paymentStatus,
            ]);

            // iPay88 expects "RECEIVEOK" response for successful processing
            return response('RECEIVEOK', 200);

        } catch (\Exception $e) {
            \Log::error('iPay88 Backend Error: ' . $e->getMessage(), [
                'refNo' => $request->input('RefNo'),
                'exception' => $e->getTraceAsString(),
            ]);

            return response('ERROR', 500);
        }
    }

    /**
     * Retry iPay88 Payment
     */
    public static function retryIPay88PaymentApi($request)
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => ['required', 'integer', 'exists:property_payments,id'],
            'payment_method' => ['nullable', 'integer'], // iPay88 payment method code (optional)
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::guard('user')->user();

            $propertyPayment = PropertyPayment::with(['property', 'propertyPaymentProperties.propertyUnit'])
                ->where('id', $request->payment_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$propertyPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property payment not found or access denied'
                ], 404);
            }

            // Only allow retry for pending (1) or failed (20) payments
            if (!in_array($propertyPayment->status, [1, 20])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending or failed payments can be retried'
                ], 422);
            }

            // Reset payment status to pending if it was failed
            if ($propertyPayment->status == 20) {
                $propertyPayment->update(['status' => 1]);
            }

            // Get iPay88 configuration
            $merchantCode = config('services.ipay88.merchant_code');
            $merchantKey = config('services.ipay88.merchant_key');
            $paymentUrl = config('services.ipay88.payment_url');
            $responseUrl = config('services.ipay88.response_url');
            $backendUrl = config('services.ipay88.backend_url');
            $currency = config('services.ipay88.currency', 'MYR');

            // Prepare payment data
            $refNo = $propertyPayment->order_no;
            $amount = $propertyPayment->amount;
            $prodDesc = $propertyPayment->order_title ?? 'Property Booking Payment';
            $userName = $propertyPayment->fullname;
            $userEmail = $propertyPayment->email;
            $userContact = ($propertyPayment->calling_code ?? '') . ($propertyPayment->phone_number ?? '');
            $remark = $propertyPayment->remarks ?? '';
            $lang = 'UTF-8';

            // Generate signature
            $signature = self::generateIPay88Signature($merchantKey, $merchantCode, $refNo, $amount, $currency);

            // Prepare iPay88 form data
            $paymentData = [
                'MerchantCode' => $merchantCode,
                'PaymentId' => $request->payment_method ?? '', // Payment method code (leave empty for selection page)
                'RefNo' => $refNo,
                'Amount' => number_format((float)$amount, 2, '.', ''),
                'Currency' => $currency,
                'ProdDesc' => $prodDesc,
                'UserName' => $userName,
                'UserEmail' => $userEmail,
                'UserContact' => $userContact,
                'Remark' => $remark,
                'Lang' => $lang,
                'Signature' => $signature,
                'ResponseURL' => $responseUrl,
                'BackendURL' => $backendUrl,
            ];

            // Update payment attempt count
            $propertyPayment->update([
                'payment_gateway' => 'ipay88',
                'payment_gateway_ref' => $refNo,
                'payment_attempt' => ($propertyPayment->payment_attempt ?? 0) + 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'iPay88 payment retry initiated successfully',
                'data' => [
                    'payment_url' => $paymentUrl,
                    'payment_data' => $paymentData,
                    'payment_id' => $propertyPayment->id,
                    'order_no' => $refNo,
                    'payment_attempt' => $propertyPayment->payment_attempt,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retry iPay88 payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retry Property Payment API (Similar to createPropertyPaymentApi)
     */
    public static function retryPropertyPaymentApi($request)
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => ['required', 'integer', 'exists:property_payments,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::guard('user')->user();

            $propertyPayment = PropertyPayment::with(['property.project', 'propertyPaymentProperties.propertyUnit'])
                ->where('id', $request->payment_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$propertyPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property payment not found or access denied'
                ], 404);
            }

            // Only allow retry for pending (1) or failed (20) payments
            if (!in_array($propertyPayment->status, [1, 20])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending or failed payments can be retried'
                ], 422);
            }

            // Reset payment status to pending if it was failed
            if ($propertyPayment->status == 20) {
                $propertyPayment->update(['status' => 1]);
            }

            $property = $propertyPayment->property;
            $project = $property->project;

            // Get units from PropertyPaymentProperty
            $validUnits = collect();
            foreach ($propertyPayment->propertyPaymentProperties as $paymentProperty) {
                if ($paymentProperty->propertyUnit) {
                    $validUnits->push($paymentProperty->propertyUnit);
                }
            }

            // Get iPay88 configuration
            $merchantCode = config('services.ipay88.merchant_code');
            $merchantKey = config('services.ipay88.merchant_key');
            $paymentUrl = config('services.ipay88.payment_url');
            $responseUrl = config('services.ipay88.response_url');
            $backendUrl = config('services.ipay88.backend_url');
            $currency = config('services.ipay88.currency', 'MYR');

            // Prepare payment data
            $refNo = $propertyPayment->order_no;
            $amount = $propertyPayment->amount;
            $prodDesc = $propertyPayment->order_title ?? 'Property Booking Payment';
            $userName = $propertyPayment->fullname;
            $userEmail = $propertyPayment->email;
            $userContact = ($propertyPayment->calling_code ?? '') . ($propertyPayment->phone_number ?? '');
            $remark = $propertyPayment->remarks ?? '';
            $lang = 'UTF-8';

            // Generate signature
            $signature = self::generateIPay88Signature($merchantKey, $merchantCode, $refNo, $amount, $currency);

            // Prepare iPay88 form data
            $paymentData = [
                'MerchantCode' => $merchantCode,
                'PaymentId' => '', // Leave empty for payment method selection page
                'RefNo' => $refNo,
                'Amount' => number_format((float)$amount, 2, '.', ''),
                'Currency' => $currency,
                'ProdDesc' => $prodDesc,
                'UserName' => $userName,
                'UserEmail' => $userEmail,
                'UserContact' => $userContact,
                'Remark' => $remark,
                'Lang' => $lang,
                'Signature' => $signature,
                'ResponseURL' => $responseUrl,
                'BackendURL' => $backendUrl,
            ];

            // Update payment attempt count
            $propertyPayment->update([
                'payment_gateway' => 'ipay88',
                'payment_gateway_ref' => $refNo,
                'payment_attempt' => ($propertyPayment->payment_attempt ?? 0) + 1,
            ]);

            // Build unit details
            $unitDetails = $validUnits->map(function ($unit) {
                return [
                    'unit_number'    => $unit->unit_number,
                    'unit_name'      => $unit->unit_name,
                    'unit_type'      => $unit->unit_type,
                    'unit_status'    => $unit->unit_status,
                    'floor'          => $unit->floor,
                    'price'          => (string) Helper::numberFormatV2($unit->selling_price, 2, true),
                    'selling_price'  => (string) Helper::numberFormatV2($unit->selling_price, 2, true),
                    'rental_price'   => $unit->rental_price ? (string) Helper::numberFormatV2($unit->rental_price, 2, true) : null,
                    'property_name'  => $unit->property->decoded_translations->en->property_name ?? null,
                    'property_details' => [
                        'bedrooms'         => $unit->bedrooms,
                        'bathrooms'        => $unit->bathrooms,
                        'balcony'          => $unit->balcony,
                        'storeroom'        => $unit->storeroom,
                        'parking_spaces'   => $unit->parking_spaces,
                        'built_up_area'    => $unit->built_up_area,
                        'maintenance_fee'  => Helper::numberFormatV2($unit->maintenance_fee, 2, true),
                    ]
                ];
            });

            // Generate redirect URL for mobile app webview
            $redirectUrl = url('api/v1/payment/ipay88/' . $propertyPayment->id);

            $data = [
                'id' => $propertyPayment->id,
                'order_no' => $propertyPayment->order_no,
                'payment_url' => $redirectUrl,
                'payment_data' => $paymentData,
                'payment_attempt' => $propertyPayment->payment_attempt,
                'project_details' => $project ? [
                    'id'           => $project->id,
                    'title'        => $project->decoded_translations->en->title ?? null,
                    'translations' => $project->decoded_translations ?? null,
                    'project_details' => $project->decoded_project_details,
                    'property_type_label' => ProjectService::getPropertyTypeLabel($project->property_type),
                    'project_type_label' => ProjectService::getPropertyTypeLabel($project->property_type),
                    'logo'         => $project->logo_path ? url($project->logo_path) : null,
                    'logo_path'    => $project->logo_path ? url($project->logo_path) : null,
                    'property_type' => $project->property_type ?? null,
                    'tenure'        => $project->tenure ?? null,
                    'location' => [
                        'address_line_1' => $project->address_line_1,
                        'address_line_2' => $project->address_line_2,
                        'address_line_3' => $project->address_line_3,
                        'state' => $project->state,
                        'postcode' => $project->postcode,
                        'latitude' => $project->latitude,
                        'longitude' => $project->longitude,
                        'locations' => $project->project_locations_details,
                    ],
                    'amenities_list'=> $project->amenities_details ?? [],
                ] : null,
                'property_details' => [
                    'id'               => $property->id,
                    'property_name'    => $property->decoded_translations->en->property_name ?? null,
                    'translations'     => $property->decoded_translations ?? null,
                    'thumbnail'        => $property->thumbnail ? url($property->thumbnail) : null,
                    'property_preview' => $property->preview ? url($property->preview) : null,
                    'property_type'    => $property->property_type,
                    'property_status'  => $property->property_status,
                    'tenure'           => $property->tenure,
                    'building_type'    => $property->building_type,
                    'furnishing_status'=> $property->furnishing_status,
                    'bedrooms'         => $property->bedrooms,
                    'bathrooms'        => $property->bathrooms,
                    'build_up_area_psf'=> Helper::numberFormatV2($property->build_up_area_psf, 2, true),
                    'selling_price_psf'=> Helper::numberFormatV2($property->selling_price_psf, 2, true),
                    'selling_price_unit'=> Helper::numberFormatV2($property->selling_price_unit, 2, true),
                    'maintenance_fee_psf'=> Helper::numberFormatV2($property->maintenance_fee_psf, 2, true),
                    'completion_date'  => $property->completion_date,
                ],
                'unit_details' => $unitDetails,
                'payment_details' => [
                    'booking_fee'    => (string) Helper::numberFormatV2($propertyPayment->amount, 2, true),
                    'processing_fee' => (string) Helper::numberFormatV2(0, 2, true),
                    'total_amount'   => (string) Helper::numberFormatV2($propertyPayment->amount, 2, true),
                    'currency'       => 'MYR',
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Property payment retry initiated successfully',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retry property payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Requery iPay88 Payment Status
     */
    public static function requeryIPay88PaymentApi($request)
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => ['required', 'integer', 'exists:property_payments,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::guard('user')->user();

            $propertyPayment = PropertyPayment::where('id', $request->payment_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$propertyPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property payment not found or access denied'
                ], 404);
            }

            // Get iPay88 configuration
            $merchantCode = config('services.ipay88.merchant_code');
            $merchantKey = config('services.ipay88.merchant_key');
            $requeryUrl = config('services.ipay88.requery_url');

            $refNo = $propertyPayment->order_no;
            $amount = $propertyPayment->amount;

            // Make requery request to iPay88
            $requeryData = [
                'MerchantCode' => $merchantCode,
                'RefNo' => $refNo,
                'Amount' => number_format((float)$amount, 2, '.', ''),
            ];

            $client = new \GuzzleHttp\Client();
            $response = $client->post($requeryUrl, [
                'form_params' => $requeryData,
                'verify' => false, // Set to true in production with proper SSL certificate
            ]);

            $responseBody = (string) $response->getBody();

            // Parse iPay88 requery response
            // Response format: Status,ErrDesc,MerchantCode,RefNo,Amount,Currency,Remark,TransId,AuthCode,Signature
            $responseParts = explode(',', $responseBody);

            if (count($responseParts) >= 10) {
                $status = $responseParts[0];
                $errDesc = $responseParts[1];
                $transId = $responseParts[7];
                $authCode = $responseParts[8];
                $signature = $responseParts[9];

                // Update payment status if needed
                $paymentStatus = ($status == '1') ? 10 : (($status == '0') ? 20 : $propertyPayment->status);

                if ($propertyPayment->status != $paymentStatus) {
                    $propertyPayment->update([
                        'transaction_id' => $transId,
                        'status' => $paymentStatus,
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Payment status retrieved successfully',
                    'data' => [
                        'order_no' => $refNo,
                        'transaction_id' => $transId,
                        'status' => $paymentStatus,
                        'status_label' => self::getPaymentStatusLabel($paymentStatus),
                        'auth_code' => $authCode,
                        'err_desc' => $errDesc,
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid requery response from iPay88',
            ], 500);

        } catch (\Exception $e) {
            \Log::error('iPay88 Requery Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to requery payment status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show iPay88 Payment Page (for mobile app webview)
     */
    public static function showIPay88PaymentPage($paymentId)
    {
        try {
            $propertyPayment = PropertyPayment::find($paymentId);

            if (!$propertyPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property payment not found'
                ], 404);
            }

            // Only allow viewing pending payments
            if ($propertyPayment->status != 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'This payment is no longer pending'
                ], 422);
            }

            // Get iPay88 configuration
            $merchantCode = config('services.ipay88.merchant_code');
            $merchantKey = config('services.ipay88.merchant_key');
            $paymentUrl = config('services.ipay88.payment_url');
            $responseUrl = config('services.ipay88.response_url');
            $backendUrl = config('services.ipay88.backend_url');
            $currency = config('services.ipay88.currency', 'MYR');

            // Prepare payment data
            $refNo = $propertyPayment->order_no;
            $amount = $propertyPayment->amount;
            $prodDesc = $propertyPayment->order_title ?? 'Property Payment';
            $userName = $propertyPayment->fullname;
            $userEmail = $propertyPayment->email;
            $userContact = ($propertyPayment->calling_code ?? '') . ($propertyPayment->phone_number ?? '');
            $remark = $propertyPayment->remarks ?? '';
            $lang = 'UTF-8';

            // Generate signature
            $signature = self::generateIPay88Signature($merchantKey, $merchantCode, $refNo, $amount, $currency);

            // Prepare iPay88 form data
            $paymentData = [
                'MerchantCode' => $merchantCode,
                'PaymentId' => '',
                'RefNo' => $refNo,
                'Amount' => number_format((float)$amount, 2, '.', ''),
                'Currency' => $currency,
                'ProdDesc' => $prodDesc,
                'UserName' => $userName,
                'UserEmail' => $userEmail,
                'UserContact' => $userContact,
                'Remark' => $remark,
                'Lang' => $lang,
                'Signature' => $signature,
                'ResponseURL' => $responseUrl,
                'BackendURL' => $backendUrl,
            ];

            // Render payment page with manual button
            $html = view('payment.ipay88-payment', [
                'paymentUrl' => $paymentUrl,
                'paymentData' => $paymentData,
                'orderNo' => $refNo,
                'amount' => number_format((float)$amount, 2),
                'currency' => $currency,
            ])->render();

            return response($html);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load payment page',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format property payment data for API response
     */
    private static function formatPropertyPaymentForApi($payment)
    {
        // Format project details
        $projectData = null;
        if ($payment->property && $payment->property->project) {
            $project = $payment->property->project;
            $projectData = [
                'id' => $project->id,
                'title' => $project->title,
                'translations' => $project->decoded_translations,
                'project_details' => $project->decoded_project_details,
                'logo' => $project->logo_path,
                'logo_path' => $project->logo_path,
                'property_type' => $project->property_type,
                'property_type_label' => ProjectService::getPropertyTypeLabel($project->property_type),
                'tenure' => $project->tenure,
                'location' => [
                    'address_line_1' => $project->address_line_1,
                    'address_line_2' => $project->address_line_2,
                    'address_line_3' => $project->address_line_3,
                    'state' => $project->state,
                    'postcode' => $project->postcode,
                    'latitude' => $project->latitude,
                    'longitude' => $project->longitude,
                    'locations' => $project->project_locations_details,
                ],
                'amenities_list' => $project->amenities_details,
            ];
        }

        // Format property details
        $propertyData = null;
        if ($payment->property) {
            $property = $payment->property;
            $propertyData = [
                'id' => $property->id,
                'property_name' => $property->property_name,
                'translations' => $property->decoded_translations,
                'property_details' => $property->decoded_property_details,
                'thumbnail' => $property->thumbnail_path,
                'property_preview' => $property->property_preview_path,
                'property_type' => $property->property_type,
                'property_status' => $property->property_status,
                'tenure' => $property->tenure,
                'building_type' => $property->building_type,
                'furnishing_status' => $property->furnishing_status,
                'bedrooms' => (int) $property->bedroom_text,
                'bathrooms' => (int) $property->bathroom_text,
                'build_up_area_psf' => $property->build_up_area_psf,
                'selling_price_psf' => $property->selling_price_psf,
                'selling_price_unit' => $property->selling_price_unit,
                'maintenance_fee_psf' => $property->maintenance_fee_psf,
                'completion_date' => $property->completion_date,
            ];
        }

        // Format unit details for all property payment units
        $unitDetails = collect();
        if ($payment->propertyPaymentProperties && $payment->propertyPaymentProperties->count() > 0) {
            $unitDetails = $payment->propertyPaymentProperties->map(function ($paymentProperty) {
                $unit = $paymentProperty->propertyUnit;
                $property = $paymentProperty->property;

                return [
                    'unit_number' => $unit ? $unit->unit_number : '-',
                    'unit_name' => $unit ? $unit->unit_name : '-',
                    'unit_type' => $unit ? $unit->unit_type : '-',
                    'unit_status' => $unit ? $unit->unit_status : '-',
                    'floor' => $unit ? $unit->floor : '-',
                    'price' => $unit ? ($unit->selling_price ?? $unit->rental_price) : $property->selling_price_unit,
                    'selling_price' => $unit ? $unit->selling_price : $property->selling_price_unit,
                    'rental_price' => $unit ? $unit->rental_price : '-',
                    'property_name' => $property ? $property->property_name : '-',
                    'property_details' => [
                        'bedrooms' => $unit ? ((int) $unit->bedrooms ?: (int) $property->bedroom_text) : $property->bedroom_text,
                        'bathrooms' => $unit ? ((int) $unit->bathrooms ?: (int) $property->bathroom_text) : $property->bathroom_text,
                        'balcony' => $unit ? ($unit->balcony ?: $property->balcony) : $property->balcony,
                        'storeroom' => $unit ? ($unit->storeroom ?: $property->storeroom) : $property->storeroom,
                        'parking_spaces' => $unit ? ($unit->parking_spaces ?: $property->parking_spaces) : $property->parking_spaces,
                        'built_up_area' => $unit ? ($unit->built_up_area ?: $property->build_up_area_psf) : $property->build_up_area_psf,
                        'maintenance_fee' => $unit ? ($unit->maintenance_fee ?: $property->maintenance_fee_psf) : $property->maintenance_fee_psf,
                    ],
                ];
            });
        } elseif ($payment->propertyUnit) {
            // Fallback for old payments with single property unit
            $unit = $payment->propertyUnit;
            $property = $payment->property;

            $unitDetails = collect([[
                'unit_number' => $unit->unit_number,
                'unit_name' => $unit->unit_name,
                'unit_type' => $unit->unit_type,
                'unit_status' => $unit->unit_status,
                'floor' => $unit->floor,
                'price' => $unit->selling_price ?? $unit->rental_price,
                'selling_price' => $unit->selling_price,
                'rental_price' => $unit->rental_price,
                'property_name' => $property->property_name,
                'property_details' => [
                    'bedrooms' => (int) $unit->bedrooms ?: (int) $property->bedroom_text,
                    'bathrooms' => (int) $unit->bathrooms ?: (int) $property->bathroom_text,
                    'balcony' => $unit->balcony ?: $property->balcony,
                    'storeroom' => $unit->storeroom ?: $property->storeroom,
                    'parking_spaces' => $unit->parking_spaces ?: $property->parking_spaces,
                    'built_up_area' => $unit->built_up_area ?: $property->build_up_area_psf,
                    'maintenance_fee' => $unit->maintenance_fee ?: $property->maintenance_fee_psf,
                ],
            ]]);
        }

        // Format payment details
        $paymentDetails = [
            'booking_fee' => Helper::numberFormatV2($payment->amount,2,true), // Assuming 100 is processing fee
            'processing_fee' => Helper::numberFormatV2(0,2,true),
            'total_amount' => Helper::numberFormatV2($payment->amount,2,true),
            'currency' => $payment->currency,
        ];

        return [
            'id' => $payment->id,
            'order_no' => $payment->order_no,
            'project_details' => $projectData,
            'property_details' => $propertyData,
            'unit_details' => $unitDetails,
            'payment_details' => $paymentDetails,
            'fullname' => $payment->fullname,
            'email' => $payment->email,
            'phone_number' => $payment->phone_number,
            'calling_code' => $payment->calling_code,
            'remarks' => $payment->remarks,
            'status' => $payment->status,
            'status_label' => self::getPaymentStatusLabel($payment->status),
            'transaction_type' => $payment->transaction_type,
            'order_title' => $payment->order_title,
            'order_detail' => $payment->order_detail,
            'created_at' => $payment->created_at,
            'updated_at' => $payment->updated_at,
        ];
    }
}