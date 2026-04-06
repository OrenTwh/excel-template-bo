<?php

namespace App\Services;

use App\Models\PropertyShortRental;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\PropertyPayment;
use App\Models\User;
use Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PropertyShortRentalService
{
    public function __construct() {}

    public static function allPropertyShortRentals($request)
    {
        $query = PropertyShortRental::with(['property', 'propertyUnit', 'user']);

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

        if (!empty($request['status'])) {
            $query->where('status', $request['status']);
        }

        if (!empty($request['created_date'])) {
            $dates = explode(' to ', $request['created_date']);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', Carbon::parse($dates[0]))
                      ->whereDate('created_at', '<=', Carbon::parse($dates[1]));
            }
        }

        if (!empty($request['check_in_date'])) {
            $dates = explode(' to ', $request['check_in_date']);
            if (count($dates) == 2) {
                $query->whereDate('check_in_date', '>=', Carbon::parse($dates[0]))
                      ->whereDate('check_in_date', '<=', Carbon::parse($dates[1]));
            }
        }

        if (!empty($request->property)) {
            $query->whereHas('property', function ($q) use ($request) {
                $q->where(function ($q2) use ($request) {
                    $q2->whereRaw('LOWER(property_name) LIKE ?', ["%{$request->property}%"])
                        ->orWhereRaw('LOWER(translations) LIKE ?', ["%{$request->property}%"]);
                });
            });
        }

        if (!empty($request['property_unit'])) {
            $query->whereHas('propertyUnit', function ($q) use ($request) {
                $q->where('unit_name', 'LIKE', '%' . $request['property_unit'] . '%');
            });
        }

        // Pagination
        $start = $request['start'] ?? 0;
        $length = $request['length'] ?? 10;

        $totalRecords = $query->count();

        $rentals = $query->offset($start)
                         ->limit($length)
                         ->orderBy('created_at', 'desc')
                         ->get();

        $rentals->map(function ($rental) {
            $rental->encrypted_id = Helper::encode($rental->id);
            $rental->created_at = $rental->created_at ? $rental->created_at->format('Y-m-d H:i:s') : '-';
            $rental->check_in_date_formatted = $rental->check_in_date ? Carbon::parse($rental->check_in_date)->format('Y-m-d') : '-';
            $rental->check_out_date_formatted = $rental->check_out_date ? Carbon::parse($rental->check_out_date)->format('Y-m-d') : '-';
            return $rental;
        });

        return [
            'property_short_rentals' => $rentals,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
        ];
    }

    public static function onePropertyShortRental($request)
    {
        $decodedId = is_numeric($request->id) 
        ? $request->id 
        : Helper::decode($request->id);

        $rental = PropertyShortRental::with(['property', 'propertyUnit', 'user'])
                                    ->find($decodedId);

        if (!$rental) {
            return null;
        }

        return $rental;
    }

    public static function createPropertyShortRental($request)
    {

        $rules = [
            'property_id' => ['required', 'exists:properties,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_in_time' => ['nullable', 'date_format:H:i'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'check_out_time' => ['nullable', 'date_format:H:i'],
            'num_of_adult' => ['nullable', 'integer', 'min:0'],
            'num_of_children' => ['nullable', 'integer', 'min:0'],
            'fullname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'calling_code' => ['nullable', 'string', 'max:5'],
            'custom_messages' => ['nullable', 'string', 'max:1000'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];

        $validator = Validator::make($request->all(), $rules);

        // Add custom validation for maximum 2 weeks
        $validator->after(function ($validator) use ($request) {
            if ($request->has('check_in_date') && $request->has('check_out_date')) {
                try {
                    $checkInDate = Carbon::parse($request->check_in_date);
                    $checkOutDate = Carbon::parse($request->check_out_date);
                    $days = $checkInDate->diffInDays($checkOutDate);

                    if ($days > 15) {
                        $validator->errors()->add('check_out_date', __('property_short_rental.max_two_weeks'));
                    }
                } catch (\Exception $e) {
                    // Date parsing error will be caught by the date validation rules
                }
            }
        });

        $attributeName = [
            'property_id' => __('property_short_rental.property_id'),
            'check_in_date' => __('property_short_rental.check_in_date'),
            'check_in_time' => __('property_short_rental.check_in_time'),
            'check_out_date' => __('property_short_rental.check_out_date'),
            'check_out_time' => __('property_short_rental.check_out_time'),
            'num_of_adult' => __('property_short_rental.num_of_adult'),
            'num_of_children' => __('property_short_rental.num_of_children'),
            'fullname' => __('property_short_rental.fullname'),
            'email' => __('property_short_rental.email'),
            'phone_number' => __('property_short_rental.phone_number'),
            'calling_code' => __('property_short_rental.calling_code'),
            'custom_messages' => __('property_short_rental.custom_messages'),
            'remarks' => __('property_short_rental.remarks'),
        ];

        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }

        $validator->setAttributeNames($attributeName)->validate();

        DB::beginTransaction();

        try {
            $checkInDate = Carbon::parse($request['check_in_date']);
            $checkOutDate = Carbon::parse($request['check_out_date']);
            $nights = $checkInDate->diffInDays($checkOutDate);

            // Find available unit for the property and date range
            $availableUnit = self::findAvailableUnit($request['property_id'], $checkInDate, $checkOutDate);

            if (!$availableUnit) {
                $validator->errors()->add('check_out_date', __('property_short_rental.no_available_units'));
                throw new ValidationException($validator);
            }

            // Calculate total amount
            $totalAmount = $availableUnit->rental_price_per_night * $nights;

            $data = [
                'property_id' => $request['property_id'],
                'property_unit_id' => $availableUnit->id,
                'user_id' => $request['user_id'] ?? auth('user')->user()->id ?? null,
                'check_in_date' => $checkInDate,
                'check_in_time' => $request['check_in_time'] ?? null,
                'check_out_date' => $checkOutDate,
                'check_out_time' => $request['check_out_time'] ?? null,
                'nights' => $nights,
                'total_amount' => $totalAmount,
                'num_of_adult' => $request['num_of_adult'] ?? null,
                'num_of_children' => $request['num_of_children'] ?? null,
                'fullname' => $request['fullname'],
                'email' => $request['email'],
                'phone_number' => $request['phone_number'],
                'calling_code' => $request['calling_code'] ?? '+60',
                'custom_messages' => $request['custom_messages'] ?? null,
                'remarks' => $request['remarks'] ?? null,
                'status' => 10,
            ];

            $rental = PropertyShortRental::create($data);

            DB::commit();
            return $rental;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public static function updatePropertyShortRental($request)
    {
        $rules = [
            'id' => ['required'],
            'property_id' => ['required', 'exists:properties,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'check_in_date' => ['required', 'date'],
            'check_in_time' => ['nullable', 'date_format:H:i'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'check_out_time' => ['nullable', 'date_format:H:i'],
            'num_of_adult' => ['nullable', 'integer', 'min:0'],
            'num_of_children' => ['nullable', 'integer', 'min:0'],
            'fullname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'calling_code' => ['nullable', 'string', 'max:5'],
            'custom_messages' => ['nullable', 'string', 'max:1000'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'integer', 'in:1,10,11,20,21'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
        ];

        $validator = Validator::make($request->all(), $rules);

        // Add custom validation for maximum 2 weeks
        $validator->after(function ($validator) use ($request) {
            if ($request->has('check_in_date') && $request->has('check_out_date')) {
                try {
                    $checkInDate = Carbon::parse($request->check_in_date);
                    $checkOutDate = Carbon::parse($request->check_out_date);
                    $days = $checkInDate->diffInDays($checkOutDate);

                    if ($days > 14) {
                        $validator->errors()->add('check_out_date', __('property_short_rental.max_two_weeks'));
                    }
                } catch (\Exception $e) {
                    // Date parsing error will be caught by the date validation rules
                }
            }
        });

        $attributeName = [
            'id' => __('property_short_rental.id'),
            'property_id' => __('property_short_rental.property_id'),
            'check_in_date' => __('property_short_rental.check_in_date'),
            'check_in_time' => __('property_short_rental.check_in_time'),
            'check_out_date' => __('property_short_rental.check_out_date'),
            'check_out_time' => __('property_short_rental.check_out_time'),
            'num_of_adult' => __('property_short_rental.num_of_adult'),
            'num_of_children' => __('property_short_rental.num_of_children'),
            'fullname' => __('property_short_rental.fullname'),
            'email' => __('property_short_rental.email'),
            'phone_number' => __('property_short_rental.phone_number'),
            'calling_code' => __('property_short_rental.calling_code'),
            'custom_messages' => __('property_short_rental.custom_messages'),
            'remarks' => __('property_short_rental.remarks'),
            'status' => __('property_short_rental.status'),
            'rating' => __('property_short_rental.rating'),
        ];

        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }

        $validator->setAttributeNames($attributeName)->validate();

        $decodedId = Helper::decode($request['id']);

        $rental = PropertyShortRental::find($decodedId);

        if (!$rental) {
            return null;
        }

        DB::beginTransaction();

        try {
            $checkInDate = Carbon::parse($request['check_in_date']);
            $checkOutDate = Carbon::parse($request['check_out_date']);
            $nights = $checkInDate->diffInDays($checkOutDate);

            // Check if property changed or dates changed, then find new available unit
            if ($rental->property_id != $request['property_id'] ||
                $rental->check_in_date->format('Y-m-d') != $checkInDate->format('Y-m-d') ||
                $rental->check_out_date->format('Y-m-d') != $checkOutDate->format('Y-m-d')) {

                $availableUnit = self::findAvailableUnit($request['property_id'], $checkInDate, $checkOutDate, $rental->id);

                if (!$availableUnit) {
                    $validator->errors()->add('check_out_date', __('property_short_rental.no_available_units'));
                    throw new ValidationException($validator);
                }

                $rental->property_unit_id = $availableUnit->id;
                $rental->total_amount = $availableUnit->rental_price_per_night * $nights;
            }

            $rental->property_id = $request['property_id'];
            $rental->user_id = $request['user_id'] ?? $rental->user_id;
            $rental->check_in_date = $checkInDate;
            $rental->check_in_time = $request['check_in_time'] ?? $rental->check_in_time;
            $rental->check_out_date = $checkOutDate;
            $rental->check_out_time = $request['check_out_time'] ?? $rental->check_out_time;
            $rental->nights = $nights;
            $rental->num_of_adult = $request['num_of_adult'] ?? $rental->num_of_adult;
            $rental->num_of_children = $request['num_of_children'] ?? $rental->num_of_children;
            $rental->fullname = $request['fullname'];
            $rental->email = $request['email'];
            $rental->phone_number = $request['phone_number'];
            $rental->calling_code = $request['calling_code'] ?? '+60';
            $rental->custom_messages = $request['custom_messages'] ?? null;
            $rental->remarks = $request['remarks'] ?? null;
            $rental->status = $request['status'] ?? $rental->status;
            $rental->rating = $request['rating'] ?? $rental->rating;

            $rental->save();

            DB::commit();
            return $rental;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public static function updatePropertyShortRentalStatus($request)
    {
        $decodedId = Helper::decode($request['id']);

        $rental = PropertyShortRental::find($decodedId);

        if (!$rental) {
            return null;
        }

        $rental->update(['status' => $request['status']]);

        return $rental;
    }

    public static function deletePropertyShortRental($request)
    {
        $decodedId = Helper::decode($request['id']);

        $rental = PropertyShortRental::find($decodedId);

        if (!$rental) {
            return null;
        }

        $rental->delete();

        return true;
    }

    /**
     * Find available unit for short rental
     */
    private static function findAvailableUnit($propertyId, $checkInDate, $checkOutDate, $excludeRentalId = null)
    {
        // Ensure valid date objects (handles both string and Carbon)
        $checkInDate = \Carbon\Carbon::parse($checkInDate)->toDateString();
        $checkOutDate = \Carbon\Carbon::parse($checkOutDate)->toDateString();

        // 1️⃣ Get all available short rental units for the given property
        $availableUnits = \App\Models\PropertyUnit::where('property_id', $propertyId)
            ->where('status', 10) // active
            ->where('unit_status', 'available')
            ->where('unit_type', 3) // short rental type
            ->get();

        // 2️⃣ Loop each unit and check if it has overlapping bookings
        foreach ($availableUnits as $unit) {

            $conflictingRentals = \App\Models\PropertyShortRental::where('property_unit_id', $unit->id)
                ->whereNotIn('status', [ 1, 20 ]) // exclude cancelled
                ->where(function ($q) use ($checkInDate, $checkOutDate) {
                    $q->where('check_in_date', '<', $checkOutDate)
                      ->where('check_out_date', '>', $checkInDate);
                });

            // Exclude the current rental if editing
            if ($excludeRentalId) {
                $conflictingRentals->where('id', '!=', $excludeRentalId);
            }

            // If no conflicts, this unit is available
            if ($conflictingRentals->count() === 0) {
                return $unit;
            }
        }

        // 3️⃣ If all units are occupied, return null
        return null;
    }

    /**
     * Find available dual key pair for short rental
     */
    private static function findAvailableDualKeyPair($propertyId, $checkInDate, $checkOutDate, $excludeRentalId = null)
    {
        // Ensure valid date objects (handles both string and Carbon)
        $checkInDate = \Carbon\Carbon::parse($checkInDate)->toDateString();
        $checkOutDate = \Carbon\Carbon::parse($checkOutDate)->toDateString();

        // Get all short rental units for the property
        $allUnits = \App\Models\PropertyUnit::where('property_id', $propertyId)
            ->where('status', 10)
            ->where('unit_status', 'available')
            ->where('unit_type', 3)
            ->get();

        // Group units into pairs (unit-1 and unit-2)
        $unitPairs = [];
        foreach ($allUnits as $unit) {
            // Extract base unit number (remove -1 or -2 suffix)
            $unitNumber = $unit->unit_number;
            if (preg_match('/^(.+)-([12])$/', $unitNumber, $matches)) {
                $baseNumber = $matches[1];
                $suffix = $matches[2];

                if (!isset($unitPairs[$baseNumber])) {
                    $unitPairs[$baseNumber] = [];
                }
                $unitPairs[$baseNumber][$suffix] = $unit;
            }
        }

        // Check each pair to see if both units are available
        foreach ($unitPairs as $baseNumber => $pair) {
            // Must have both unit-1 and unit-2
            if (!isset($pair['1']) || !isset($pair['2'])) {
                continue;
            }

            $unit1 = $pair['1'];
            $unit2 = $pair['2'];

            // Check if unit 1 has conflicts
            $conflicts1 = \App\Models\PropertyShortRental::where('property_unit_id', $unit1->id)
                ->where('status', '!=', 21)
                ->where(function ($q) use ($checkInDate, $checkOutDate) {
                    $q->where('check_in_date', '<', $checkOutDate)
                      ->where('check_out_date', '>', $checkInDate);
                });

            if ($excludeRentalId) {
                $conflicts1->where('id', '!=', $excludeRentalId);
            }

            // Check if unit 2 has conflicts
            $conflicts2 = \App\Models\PropertyShortRental::where('property_unit_id', $unit2->id)
                ->where('status', '!=', 21)
                ->where(function ($q) use ($checkInDate, $checkOutDate) {
                    $q->where('check_in_date', '<', $checkOutDate)
                      ->where('check_out_date', '>', $checkInDate);
                });

            if ($excludeRentalId) {
                $conflicts2->where('id', '!=', $excludeRentalId);
            }

            // If both units are available, return the pair
            if ($conflicts1->count() === 0 && $conflicts2->count() === 0) {
                return [$unit1, $unit2];
            }
        }

        // No available pair found
        return null;
    }
    

    // ============================================
    // API Methods
    // ============================================

    /**
     * Check availability API
     */
    public static function checkAvailabilityApi($request)
    {
        $rules = [
            'property_id' => ['required', 'exists:properties,id'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'rent_all' => ['nullable', 'boolean'],
        ];

        $validator = Validator::make($request->all(), $rules);

        // Add custom validation for maximum 2 weeks
        $validator->after(function ($validator) use ($request) {
            if ($request->has('check_in_date') && $request->has('check_out_date')) {
                try {
                    $checkInDate = Carbon::parse($request->check_in_date);
                    $checkOutDate = Carbon::parse($request->check_out_date);
                    $days = $checkInDate->diffInDays($checkOutDate);

                    if ($days > 14) {
                        $validator->errors()->add('check_out_date', __('property_short_rental.max_two_weeks'));
                    }
                } catch (\Exception $e) {
                    // Date parsing error will be caught by the date validation rules
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $checkInDate = Carbon::parse($request['check_in_date']);
            $checkOutDate = Carbon::parse($request['check_out_date']);

            $property = Property::find($request['property_id']);
            $isDualKey = $property && $property->is_dual_key_unit == true;
            $rentAll = $request->input('rent_all', false) === true || $request->input('rent_all') === 'true';

            $available = false;
            $message = '';
            $availableUnitsCount = 0;

            if ($isDualKey && $rentAll) {
                // Check if both units are available
                $availablePair = self::findAvailableDualKeyPair($request['property_id'], $checkInDate, $checkOutDate);

                if ($availablePair && count($availablePair) >= 2) {
                    $available = true;
                    $availableUnitsCount = 2;
                    $message = 'Both units available for selected dates';
                } else {
                    $message = 'Both units are not available for selected dates. Only single unit may be available.';
                }
            } else {
                // Check for single unit availability
                $availableUnit = self::findAvailableUnit($request['property_id'], $checkInDate, $checkOutDate);

                if ($availableUnit) {
                    $available = true;
                    $availableUnitsCount = 1;
                    $message = 'Units available for selected dates';
                } else {
                    $message = __('property_short_rental.no_available_units');
                }
            }

            return response()->json([
                'success' => true,
                'available' => $available,
                'available_units_count' => $availableUnitsCount,
                'is_dual_key' => $isDualKey,
                'rent_all' => $isDualKey && $rentAll,
                'message' => $message
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview short rental API
     */
    public static function previewShortRentalApi($request)
    {
        $rules = [
            'property_id' => ['required', 'exists:properties,id'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'rent_all' => ['nullable', 'boolean'],
        ];

        $validator = Validator::make($request->all(), $rules);

        // Add custom validation for maximum 2 weeks
        $validator->after(function ($validator) use ($request) {
            if ($request->has('check_in_date') && $request->has('check_out_date')) {
                try {
                    $checkInDate = Carbon::parse($request->check_in_date);
                    $checkOutDate = Carbon::parse($request->check_out_date);
                    $days = $checkInDate->diffInDays($checkOutDate);

                    if ($days > 14) {
                        $validator->errors()->add('check_out_date', __('property_short_rental.max_two_weeks'));
                    }
                } catch (\Exception $e) {
                    // Date parsing error will be caught by the date validation rules
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $checkInDate = Carbon::parse($request['check_in_date']);
            $checkOutDate = Carbon::parse($request['check_out_date']);
            $nights = $checkInDate->diffInDays($checkOutDate);

            $property = Property::with(['project'])->find($request['property_id']);
            $isDualKey = $property && $property->is_dual_key_unit == true;
            $rentAll = $request->input('rent_all', false) === true || $request->input('rent_all') === 'true';

            $totalAmount = 0;
            $pricePerNight = 0;
            $unitsCount = 1;
            $unitDetails = [];

            if ($isDualKey && $rentAll) {
                // Preview for dual key units
                $availablePair = self::findAvailableDualKeyPair($request['property_id'], $checkInDate, $checkOutDate);

                if (!$availablePair || count($availablePair) < 2) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Both units are not available for the selected dates. Please try single unit rental.'
                    ], 422);
                }

                foreach ($availablePair as $unit) {
                    $pricePerNight += $unit->rental_price_per_night;
                    $totalAmount += $unit->rental_price_per_night * $nights;
                    $unitDetails[] = [
                        'unit_number' => $unit->unit_number,
                        'unit_name' => $unit->unit_name,
                        'price_per_night' => number_format($unit->rental_price_per_night, 2, '.', ''),
                    ];
                }
                $unitsCount = 2;
            } else {
                // Preview for single unit
                $availableUnit = self::findAvailableUnit($request['property_id'], $checkInDate, $checkOutDate);

                if (!$availableUnit) {
                    return response()->json([
                        'success' => false,
                        'message' => __('property_short_rental.no_available_units')
                    ], 422);
                }

                $pricePerNight = $availableUnit->rental_price_per_night;
                $totalAmount = $availableUnit->rental_price_per_night * $nights;
                $unitDetails[] = [
                    'unit_number' => $availableUnit->unit_number,
                    'unit_name' => $availableUnit->unit_name,
                    'price_per_night' => number_format($availableUnit->rental_price_per_night, 2, '.', ''),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'property' => [
                        'id' => $property->id,
                        'property_name' => $property->property_name,
                        'project_name' => $property->project->project_name ?? null,
                        'is_dual_key' => $isDualKey,
                    ],
                    'check_in_date' => $checkInDate->format('Y-m-d'),
                    'check_out_date' => $checkOutDate->format('Y-m-d'),
                    'nights' => $nights,
                    'units_count' => $unitsCount,
                    'unit_details' => $unitDetails,
                    'price_per_night' => number_format($pricePerNight, 2, '.', ''),
                    'total_amount' => number_format($totalAmount, 2, '.', ''),
                    'rent_all' => $isDualKey && $rentAll,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create short rental API
     */
    public static function createShortRentalApi($request)
    {
        $rules = [
            'property_id' => ['required', 'exists:properties,id'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_in_time' => ['nullable', 'date_format:H:i'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'check_out_time' => ['nullable', 'date_format:H:i'],
            'num_of_adult' => ['nullable', 'integer', 'min:0'],
            'num_of_children' => ['nullable', 'integer', 'min:0'],
            'fullname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'calling_code' => ['nullable', 'string', 'max:5'],
            'custom_messages' => ['nullable', 'string', 'max:1000'],
            'rent_all' => ['nullable', 'boolean'],
        ];

        $validator = Validator::make($request->all(), $rules);

        // Add custom validation for maximum 2 weeks
        $validator->after(function ($validator) use ($request) {
            if ($request->has('check_in_date') && $request->has('check_out_date')) {
                try {
                    $checkInDate = Carbon::parse($request->check_in_date);
                    $checkOutDate = Carbon::parse($request->check_out_date);
                    $days = $checkInDate->diffInDays($checkOutDate);

                    if ($days > 15) {
                        $validator->errors()->add('check_out_date', __('property_short_rental.max_two_weeks'));
                    }
                } catch (\Exception $e) {
                    // Date parsing error will be caught by the date validation rules
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $checkInDate = Carbon::parse($request['check_in_date']);
            $checkOutDate = Carbon::parse($request['check_out_date']);
            $nights = $checkInDate->diffInDays($checkOutDate);

            // Check if property is dual key
            $property = Property::find($request['property_id']);
            $isDualKey = $property && $property->is_dual_key_unit == true;
            $rentAll = $request->input('rent_all', false) === true || $request->input('rent_all') === 'true';

            $unitsToBook = [];
            $totalAmount = 0;

            if ($isDualKey && $rentAll) {
                // Find both available units (dual key pair)
                $availablePair = self::findAvailableDualKeyPair($request['property_id'], $checkInDate, $checkOutDate);

                if (!$availablePair || count($availablePair) < 2) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Both units are not available for the selected dates. Please select single unit or choose different dates.'
                    ], 422);
                }

                $unitsToBook = $availablePair;
                foreach ($availablePair as $unit) {
                    $totalAmount += $unit->rental_price_per_night * $nights;
                }
            } else {
                // Find single available unit
                $availableUnit = self::findAvailableUnit($request['property_id'], $checkInDate, $checkOutDate);

                if (!$availableUnit) {
                    return response()->json([
                        'success' => false,
                        'message' => __('property_short_rental.no_available_units')
                    ], 422);
                }

                $unitsToBook = [$availableUnit];
                $totalAmount = $availableUnit->rental_price_per_night * $nights;
            }
            $totalAmount = 1;
            // Create rental records for all units
            $rentals = [];
            foreach ($unitsToBook as $unit) {
                $data = [
                    'property_id' => $request['property_id'],
                    'property_unit_id' => $unit->id,
                    'user_id' => auth('user')->user()->id,
                    'check_in_date' => $checkInDate,
                    'check_in_time' => $request['check_in_time'] ?? null,
                    'check_out_date' => $checkOutDate,
                    'check_out_time' => $request['check_out_time'] ?? null,
                    'nights' => $nights,
                    'total_amount' => $totalAmount,
                    'num_of_adult' => $request['num_of_adult'] ?? null,
                    'num_of_children' => $request['num_of_children'] ?? null,
                    'fullname' => $request['fullname'],
                    'email' => $request['email'],
                    'phone_number' => $request['phone_number'],
                    'calling_code' => $request['calling_code'] ?? '+60',
                    'custom_messages' => $request['custom_messages'] ?? null,
                    'status' => 1, // Pending
                ];

                $rental = PropertyShortRental::create($data);
                $rental = PropertyShortRental::with(['property.project', 'propertyUnit'])->find($rental->id);
                $rentals[] = $rental;
            }

            // Prepare iPay88 payment integration
            $ipay88Credentials = Helper::getIPay88Credentials();
            $merchantCode = $ipay88Credentials['merchant_code'];
            $merchantKey = $ipay88Credentials['merchant_key'];
            $paymentUrl = $ipay88Credentials['payment_url'];
            $responseUrl = $ipay88Credentials['response_url'];
            $backendUrl = $ipay88Credentials['backend_url'];
            $currency = $ipay88Credentials['currency'];

            // Generate reference number for the first rental (primary booking)
            $primaryRental = $rentals[0];
            $refNo = 'SR-' . str_pad($primaryRental->id, 8, '0', STR_PAD_LEFT);
            $amount = $totalAmount;
            $prodDesc = 'Short Rental Booking - ' . ($primaryRental->property->property_name ?? 'Property');
            $userName = $request['fullname'];
            $userEmail = $request['email'];
            $userContact = ($request['calling_code'] ?? '+60') . ($request['phone_number'] ?? '');
            $remark = $request['custom_messages'] ?? '';
            $lang = 'UTF-8';

            // Update all rentals with order details and payment gateway info
            foreach ($rentals as $rental) {
                $rental->update([
                    'order_no' => $refNo,
                    'order_title' => $prodDesc,
                    'payment_gateway' => 'ipay88',
                    'payment_gateway_ref' => $refNo,
                    'payment_attempt' => 1,
                ]);
            }

            // Create PropertyPayment record for short rental
            $propertyPayment = PropertyPayment::create([
                'user_id' => auth('user')->user()->id,
                'property_id' => $request['property_id'],
                'property_unit_id' => $unitsToBook[0]->id,
                'fullname' => $request['fullname'],
                'email' => $request['email'],
                'phone_number' => $request['phone_number'],
                'calling_code' => $request['calling_code'] ?? '+60',
                'remarks' => $request['custom_messages'] ?? null,
                'order_no' => $refNo,
                'order_title' => $prodDesc,
                'order_detail' => 'Short rental for ' . $nights . ' night(s)',
                'amount' => $totalAmount,
                'currency' => $currency,
                'transaction_type' => 3, // Short-Rental Payment
                'status' => 1, // Pending
                'payment_gateway' => 'ipay88',
                'payment_gateway_ref' => $refNo,
                'payment_attempt' => 1,
            ]);

            // Generate iPay88 signature
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
                'SignatureType' => 'HMACSHA512',
                'ResponseURL' => $responseUrl,
                'BackendURL' => $backendUrl,
            ];

            DB::commit();

            // Generate redirect URL for mobile app webview (points to iPay88 payment page)
            // Use production URL when in live mode for iPay88 referrer validation
            $redirectUrl = \Helper::getPaymentGatewayUrl('api/v1/payment/ipay88/' . $propertyPayment->id);

            $message = count($rentals) > 1
                ? 'Short rental booking created successfully for both units (dual key)'
                : 'Short rental booking created successfully';

            $responseData = count($rentals) === 1
                ? self::formatShortRentalForApi($rentals[0])
                : [
                    'bookings' => array_map(fn($r) => self::formatShortRentalForApi($r), $rentals),
                    'is_dual_key' => true,
                    'units_booked' => count($rentals),
                    'total_amount' => number_format($totalAmount, 2, '.', ''),
                ];
            
            // Add payment information to response
            $responseData['payment_url'] = $redirectUrl;
            $responseData['payment_data'] = $paymentData;
            $responseData['order_no'] = $refNo;
            $responseData['property_payment_id'] = $propertyPayment->id;

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $responseData
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
            ], 500);
        }
    }

    /**
     * Retry rental payment API
     */
    public static function retryRentalPaymentApi($request)
    {
        $validator = Validator::make($request->all(), [
            'rental_id' => ['required', 'integer', 'exists:property_short_rentals,id'],
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

            $rental = PropertyShortRental::with(['property.project', 'propertyUnit'])
                ->where('id', $request->rental_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$rental) {
                return response()->json([
                    'success' => false,
                    'message' => 'Short rental not found or access denied'
                ], 404);
            }

            // Only allow retry for pending (1 or 10) or failed (20) rentals
            if (!in_array($rental->status, [1, 20])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending or failed rentals can be retried'
                ], 422);
            }

            // Reset rental status to pending if it was failed
            if ($rental->status == 20) {
                $rental->update(['status' => 1]);
            }

            $property = $rental->property;
            $project = $property->project;
            $propertyUnit = $rental->propertyUnit;

            // Get iPay88 configuration
            $ipay88Credentials = Helper::getIPay88Credentials();
            $merchantCode = $ipay88Credentials['merchant_code'];
            $merchantKey = $ipay88Credentials['merchant_key'];
            $paymentUrl = $ipay88Credentials['payment_url'];
            $responseUrl = $ipay88Credentials['response_url'];
            $backendUrl = $ipay88Credentials['backend_url'];
            $currency = $ipay88Credentials['currency'];

            // Prepare payment data
            $refNo = $rental->order_no ?? 'SR-' . str_pad($rental->id, 8, '0', STR_PAD_LEFT);
            $amount = $rental->total_amount;
            $prodDesc = 'Short Rental Booking - ' . ($property->property_name ?? 'Property');
            $userName = $rental->fullname;
            $userEmail = $rental->email;
            $userContact = ($rental->calling_code ?? '') . ($rental->phone_number ?? '');
            $remark = $rental->custom_messages ?? '';
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
                'SignatureType' => 'HMACSHA512',
                'ResponseURL' => $responseUrl,
                'BackendURL' => $backendUrl,
            ];

            // Update payment attempt count
            $rental->update([
                'order_no' => $refNo,
                'order_title' => $prodDesc,
                'payment_gateway' => 'ipay88',
                'payment_gateway_ref' => $refNo,
                'payment_attempt' => ($rental->payment_attempt ?? 0) + 1,
            ]);

            // Find or create PropertyPayment for this rental
            $propertyPayment = PropertyPayment::where('order_no', $refNo)->first();

            if (!$propertyPayment) {
                // Create new PropertyPayment if not exists
                $propertyPayment = PropertyPayment::create([
                    'user_id' => auth('user')->user()->id,
                    'property_id' => $property->id,
                    'property_unit_id' => $propertyUnit ? $propertyUnit->id : null,
                    'fullname' => $rental->fullname,
                    'email' => $rental->email,
                    'phone_number' => $rental->phone_number,
                    'calling_code' => $rental->calling_code ?? '+60',
                    'remarks' => $rental->custom_messages ?? null,
                    'order_no' => $refNo,
                    'order_title' => $prodDesc,
                    'order_detail' => 'Short rental retry payment',
                    'amount' => $rental->total_amount,
                    'currency' => $currency,
                    'transaction_type' => 3, // Short-Rental Payment
                    'status' => 1, // Pending
                    'payment_gateway' => 'ipay88',
                    'payment_gateway_ref' => $refNo,
                    'payment_attempt' => $rental->payment_attempt,
                ]);
            } else {
                // Update existing PropertyPayment
                $propertyPayment->update([
                    'payment_gateway_ref' => $refNo,
                    'payment_attempt' => $rental->payment_attempt,
                    'status' => 1, // Reset to pending
                ]);
            }

            // Generate redirect URL for mobile app webview (points to iPay88 payment page)
            // Use production URL when in live mode for iPay88 referrer validation
            $redirectUrl = \Helper::getPaymentGatewayUrl('api/v1/payment/ipay88/' . $propertyPayment->id);

            // Build property details
            $propertyDetails = [
                'id' => $property->id,
                'property_name' => $property->decoded_translations->en->property_name ?? null,
                'translations' => $property->decoded_translations ?? null,
                'thumbnail' => $property->thumbnail ? url($property->thumbnail) : null,
                'property_preview' => $property->preview ? url($property->preview) : null,
                'property_type' => $property->property_type,
                'property_status' => $property->property_status,
                'tenure' => $property->tenure,
                'building_type' => $property->building_type,
                'furnishing_status' => $property->furnishing_status,
                'bedrooms' => $property->bedrooms,
                'bathrooms' => $property->bathrooms,
            ];

            // Build unit details if available
            $unitDetails = null;
            if ($propertyUnit) {
                $unitDetails = [
                    'unit_number' => $propertyUnit->unit_number,
                    'unit_name' => $propertyUnit->unit_name,
                    'unit_type' => $propertyUnit->unit_type,
                    'unit_status' => $propertyUnit->unit_status,
                    'floor' => $propertyUnit->floor,
                    'rental_price_per_night' => $propertyUnit->rental_price_per_night ? (string) Helper::numberFormatV2($propertyUnit->rental_price_per_night, 2, true) : null,
                    'property_details' => [
                        'bedrooms' => $propertyUnit->bedrooms,
                        'bathrooms' => $propertyUnit->bathrooms,
                        'balcony' => $propertyUnit->balcony,
                        'storeroom' => $propertyUnit->storeroom,
                        'parking_spaces' => $propertyUnit->parking_spaces,
                        'built_up_area' => $propertyUnit->built_up_area,
                        'maintenance_fee' => Helper::numberFormatV2($propertyUnit->maintenance_fee, 2, true),
                    ]
                ];
            }

            // Build project details if available
            $projectDetails = null;
            if ($project) {
                $projectDetails = [
                    'id' => $project->id,
                    'title' => $project->decoded_translations->en->title ?? null,
                    'translations' => $project->decoded_translations ?? null,
                    'project_details' => $project->decoded_project_details,
                    'property_type_label' => ProjectService::getPropertyTypeLabel($project->property_type),
                    'project_type_label' => ProjectService::getPropertyTypeLabel($project->property_type),
                    'logo' => $project->logo_path ? url($project->logo_path) : null,
                    'logo_path' => $project->logo_path ? url($project->logo_path) : null,
                    'property_type' => $project->property_type ?? null,
                    'tenure' => $project->tenure ?? null,
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
                    'amenities_list' => $project->amenities_details ?? [],
                ];
            }

            $data = [
                'id' => $rental->id,
                'order_no' => $refNo,
                'payment_url' => $redirectUrl,
                'payment_data' => $paymentData,
                'payment_attempt' => $rental->payment_attempt,
                'project_details' => $projectDetails,
                'property_details' => $propertyDetails,
                'unit_details' => $unitDetails,
                'rental_details' => [
                    'check_in_date' => $rental->check_in_date ? $rental->check_in_date->format('Y-m-d') : null,
                    'check_in_time' => $rental->check_in_time,
                    'check_out_date' => $rental->check_out_date ? $rental->check_out_date->format('Y-m-d') : null,
                    'check_out_time' => $rental->check_out_time,
                    'nights' => $rental->nights,
                    'num_of_adult' => $rental->num_of_adult,
                    'num_of_children' => $rental->num_of_children,
                ],
                'payment_details' => [
                    'booking_fee' => (string) Helper::numberFormatV2($rental->total_amount, 2, true),
                    'processing_fee' => (string) Helper::numberFormatV2(0, 2, true),
                    'total_amount' => (string) Helper::numberFormatV2($rental->total_amount, 2, true),
                    'currency' => 'MYR',
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Short rental payment retry initiated successfully',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retry rental payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user short rentals API
     */
    public static function getUserShortRentalsApi($request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|integer|in:1,10,11,20,21',
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
            $user = auth('user')->user();

            $query = PropertyShortRental::with(['property.project', 'propertyUnit'])
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

            $rentals = $query->paginate($perPage);

            // Transform the data for API response
            $rentals->getCollection()->transform(function ($rental) {
                return self::formatShortRentalForApi($rental);
            });

            return response()->json([
                'success' => true,
                'data' => $rentals->items(),
                'pagination' => [
                    'current_page' => $rentals->currentPage(),
                    'last_page' => $rentals->lastPage(),
                    'per_page' => $rentals->perPage(),
                    'total' => $rentals->total(),
                    'from' => $rentals->firstItem(),
                    'to' => $rentals->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching short rentals',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get short rental details API
     */
    public static function getShortRentalApi($request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:property_short_rentals,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Short rental not found',
                'errors' => $validator->errors()
            ], 404);
        }

        try {
            $user = auth('user')->user();

            $rental = PropertyShortRental::with(['property.project', 'propertyUnit'])
                ->where('id', $request->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$rental) {
                return response()->json([
                    'success' => false,
                    'message' => 'Short rental not found or does not belong to this user'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => self::formatShortRentalForApi($rental)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching short rental details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update short rental API
     */
    public static function updateShortRentalApi($request)
    {
        $rules = [
            'check_in_date' => ['nullable', 'date', 'after_or_equal:today'],
            'check_in_time' => ['nullable', 'date_format:H:i'],
            'check_out_date' => ['nullable', 'date', 'after:check_in_date'],
            'check_out_time' => ['nullable', 'date_format:H:i'],
            'num_of_adult' => ['nullable', 'integer', 'min:0'],
            'num_of_children' => ['nullable', 'integer', 'min:0'],
            'fullname' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'calling_code' => ['nullable', 'string', 'max:5'],
            'custom_messages' => ['nullable', 'string', 'max:1000'],
        ];

        $validator = Validator::make($request->all(), $rules);

        // Add custom validation for maximum 2 weeks
        $validator->after(function ($validator) use ($request) {
            if ($request->has('check_in_date') && $request->has('check_out_date')) {
                try {
                    $checkInDate = Carbon::parse($request->check_in_date);
                    $checkOutDate = Carbon::parse($request->check_out_date);
                    $days = $checkInDate->diffInDays($checkOutDate);

                    if ($days > 14) {
                        $validator->errors()->add('check_out_date', __('property_short_rental.max_two_weeks'));
                    }
                } catch (\Exception $e) {
                    // Date parsing error will be caught by the date validation rules
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $rental = PropertyShortRental::where('id', $request->id)
                ->where('user_id', auth('user')->user()->id)
                ->first();

            if (!$rental) {
                return response()->json([
                    'success' => false,
                    'message' => __('property_short_rental.not_found')
                ], 404);
            }

            // Check if rental can be updated (not completed or cancelled)
            if (in_array($rental->status, [11, 21])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update completed or cancelled rental'
                ], 422);
            }

            // Update dates if provided
            if ($request->has('check_in_date') && $request->has('check_out_date')) {
                $checkInDate = Carbon::parse($request['check_in_date']);
                $checkOutDate = Carbon::parse($request['check_out_date']);
                $nights = $checkInDate->diffInDays($checkOutDate);

                // Check if dates changed, find new available unit
                if ($rental->check_in_date->format('Y-m-d') != $checkInDate->format('Y-m-d') ||
                    $rental->check_out_date->format('Y-m-d') != $checkOutDate->format('Y-m-d')) {

                    $availableUnit = self::findAvailableUnit($rental->property_id, $checkInDate, $checkOutDate, $rental->id);

                    if (!$availableUnit) {
                        return response()->json([
                            'success' => false,
                            'message' => __('property_short_rental.no_available_units')
                        ], 422);
                    }

                    $rental->property_unit_id = $availableUnit->id;
                    $rental->total_amount = $availableUnit->rental_price_per_night * $nights;
                }

                $rental->check_in_date = $checkInDate;
                $rental->check_out_date = $checkOutDate;
                $rental->nights = $nights;
            }

            // Update times if provided
            if ($request->has('check_in_time')) $rental->check_in_time = $request->check_in_time;
            if ($request->has('check_out_time')) $rental->check_out_time = $request->check_out_time;

            // Update guest numbers if provided
            if ($request->has('num_of_adult')) $rental->num_of_adult = $request->num_of_adult;
            if ($request->has('num_of_children')) $rental->num_of_children = $request->num_of_children;

            // Update other fields
            if ($request->has('fullname')) $rental->fullname = $request->fullname;
            $rental->email = $request->email;
            $rental->phone_number = $request->phone_number;
            if ($request->has('calling_code')) $rental->calling_code = $request->calling_code;
            if ($request->has('custom_messages')) $rental->custom_messages = $request->custom_messages;

            $rental->save();
            $rental->load(['property.project', 'propertyUnit']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Short rental updated successfully',
                'data' => self::formatShortRentalForApi($rental)
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel short rental API
     */
    public static function cancelShortRentalApi($request)
    {
        DB::beginTransaction();

        try {
            $rental = PropertyShortRental::where('id', $request->id)
                ->where('user_id', auth('user')->user()->id)
                ->first();

            if (!$rental) {
                return response()->json([
                    'success' => false,
                    'message' => __('property_short_rental.not_found')
                ], 404);
            }

            // Check if already cancelled
            if ($rental->status == 20) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rental is already cancelled'
                ], 422);
            }

            $rental->update(['status' => 20]); // Cancelled

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Short rental cancelled successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Checkout short rental API
     */
    public static function checkoutShortRentalApi($request)
    {
        DB::beginTransaction();

        try {
            $rental = PropertyShortRental::where('id', $request->id)
                ->where('user_id', auth('user')->user()->id)
                ->first();

            if (!$rental) {
                return response()->json([
                    'success' => false,
                    'message' => __('property_short_rental.not_found')
                ], 404);
            }

            // Check if already completed/checked out
            if ($rental->status == 11) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rental is already checked out'
                ], 422);
            }

            // Check if cancelled
            if ($rental->status == 20) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot checkout a cancelled rental'
                ], 422);
            }

            $rental->update(['status' => 11]); // Completed/Checkout

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Short rental checked out successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit rating and review API
     */
    public static function submitRatingApi($request)
    {
        DB::beginTransaction();

        try {
            // Validation
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|exists:property_short_rentals,id',
                'rating' => 'required|integer|min:1|max:5',
                'reviews' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $rental = PropertyShortRental::where('id', $request->id)
                ->where('user_id', auth('user')->user()->id)
                ->first();

            if (!$rental) {
                return response()->json([
                    'success' => false,
                    'message' => __('property_short_rental.not_found')
                ], 404);
            }

            // Check if rental is completed
            if ($rental->status != 11) {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only submit rating for completed rentals'
                ], 422);
            }

            // Check if already rated
            if ($rental->rating !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rating has already been submitted for this rental'
                ], 422);
            }

            $rental->update([
                'rating' => $request->rating,
                'reviews' => $request->reviews
            ]);

            // Update property's average rating
            if ($rental->property) {
                $avgRating = PropertyShortRental::where('property_id', $rental->property_id)
                    ->whereNotNull('rating')
                    ->where('rating', '>', 0)
                    ->avg('rating');

                $rental->property->update([
                    'short_rental_rating' => round($avgRating, 2)
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Rating and review submitted successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get status options API
     */
    public static function getStatusOptionsApi($request)
    {
        try {
            $statuses = [
                ['value' => 1, 'label' => __('datatables.pending')],
                ['value' => 10, 'label' => __('datatables.activated')],
                ['value' => 11, 'label' => __('datatables.completed')],
                ['value' => 20, 'label' => __('datatables.suspended')],
                ['value' => 20, 'label' => __('datatables.cancelled')],
            ];

            return response()->json([
                'success' => true,
                'data' => $statuses
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format short rental for API response (matching BookingService format)
     */
    private static function formatShortRentalForApi($rental)
    {
        // Format project details (oneProjectApi format)
        $projectData = null;
        if ($rental->property && $rental->property->project) {
            $project = $rental->property->project;
            $projectData = [
                'id' => $project->id,
                'title' => $project->title,
                'translations' => $project->decoded_translations,
                'project_details' => $project->decoded_project_details,
                'logo' => $project->logo_path,
                'logo_path' => $project->logo_path,
                'property_type' => $project->property_type,
                'property_type_label' => \App\Services\ProjectService::getPropertyTypeLabel($project->property_type),
                // 'tenure' => $project->tenure,
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

        // Format property details (onePropertyApi format)
        $propertyData = null;
        if ($rental->property) {
            $property = $rental->property;
            $propertyData = [
                'id' => $property->id,
                'property_name' => $property->property_name,
                'translations' => $property->decoded_translations,
                'property_details' => $property->decoded_property_details,
                'thumbnail' => $property->thumbnail_path,
                'property_preview' => $property->property_preview_path,
                'property_type' => $property->property_type,
                'property_status' => $property->property_status,
                // 'tenure' => $property->tenure,
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

        // Format unit details
        $unitDetails = collect();
        if ($rental->propertyUnit) {
            $unit = $rental->propertyUnit;
            $property = $rental->property;

            $unitDetails = collect([[
                'unit_number' => $unit->unit_number,
                'unit_name' => $unit->unit_name,
                'unit_type' => $unit->unit_type,
                'unit_status' => $unit->unit_status,
                'floor' => $unit->floor,
                'price' => $unit->rental_price_per_night,
                'selling_price' => $unit->selling_price,
                'rental_price' => $unit->rental_price,
                'rental_price_per_night' => $unit->rental_price_per_night,
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

        // no tax for now
        $taxAmount = 0;

        // Format payment details
        $paymentDetails = [
            'booking_fee' => Helper::numberFormatV2( $rental->total_amount, 2, true ),
            'subtotal' => Helper::numberFormatV2( $rental->total_amount, 2, true ),
            "processing_fee" => Helper::numberFormatV2( 0, 2, true ),
            'tax' => Helper::numberFormatV2( 0, 2, true ),
            'total_amount' => Helper::numberFormatV2( $rental->total_amount, 2, true ),
            'currency' => 'MYR',
        ];

        // Get payment_url for pending rentals
        $paymentUrl = null;
        $propertyPaymentId = null;
        if ($rental->status == 1 && $rental->order_no) {
            $propertyPayment = PropertyPayment::where('order_no', $rental->order_no)->first();
            if ($propertyPayment && $propertyPayment->status == 1) {
                // Use production URL when in live mode for iPay88 referrer validation
                $paymentUrl = \Helper::getPaymentGatewayUrl('api/v1/payment/ipay88/' . $propertyPayment->id);
                $propertyPaymentId = $propertyPayment->id;
            }
        }

        return [
            'id' => $rental->id,
            'project_details' => $projectData,
            'property_details' => $propertyData,
            'unit_details' => $unitDetails,
            'fullname' => $rental->fullname,
            'email' => $rental->email,
            'country' => optional( $rental->user?->nationalityInfo )->name,
            'phone_number' => $rental->phone_number,
            'calling_code' => $rental->calling_code,
            'check_in_date' => $rental->check_in_date ? Carbon::parse($rental->check_in_date)->format('Y-m-d') : null,
            'check_in_time' => $rental->check_in_time ?? null,
            'check_out_date' => $rental->check_out_date ? Carbon::parse($rental->check_out_date)->format('Y-m-d') : null,
            'check_out_time' => $rental->check_out_time ?? null,
            'nights' => $rental->nights,
            'payment_details' => $paymentDetails,
            'order_no' => $rental->order_no,
            'payment_url' => $paymentUrl,
            'property_payment_id' => $propertyPaymentId,
            'payment_method' => 'Online Banking',
            'num_of_adult' => $rental->num_of_adult ?? null,
            'num_of_children' => $rental->num_of_children ?? null,
            'custom_messages' => $rental->custom_messages,
            'remarks' => $rental->remarks ?? null,
            'status' => $rental->status,
            'status_label' => self::getShortRentalStatusLabel($rental->status),
            'created_at' => $rental->created_at,
            'updated_at' => $rental->updated_at,
        ];
    }

    /**
     * Get status label for short rental
     */
    private static function getShortRentalStatusLabel($status)
    {
        $labels = [
            1 => 'Pending',
            10 => 'Activated',
            11 => 'Completed',
            20 => 'Suspended',
            20 => 'Cancelled',
        ];

        return $labels[$status] ?? 'Unknown';
    }

    /**
     * Generate iPay88 signature for payment request
     */
    private static function generateIPay88Signature($merchantKey, $merchantCode, $refNo, $amount, $currency)
    {
        // iPay88 Signature: HMACSHA512(MerchantKey + MerchantCode + RefNo + Amount + Currency, MerchantKey)
        // Amount must be formatted as 2 decimal places, then strip dots and commas
        $amount = number_format((float)$amount, 2, '.', '');
        $amount = strtr($amount, array('.' => '', ',' => ''));
        $source = $merchantKey . $merchantCode . $refNo . $amount . $currency;
        return hash_hmac('sha512', $source, $merchantKey);
    }
}
