<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
};

use Helper;

use App\Models\{
    UserVisit,
    User,
    VisitDetail,
    TicketType,
    DateClosure,
    ApiLog,
};

use App\Services\TicketCapacityService;

use Carbon\Carbon;

class VisitService
{

    public static function createVisit( $request ) {

        $validator = Validator::make( $request->all(), [
            'user_id' => [ 'nullable', 'exists:users,id' ],
            'visit_date' => [
                'required',
                'date',
                'after_or_equal:today',
                'before_or_equal:today',
            ],
            'reference' => [ 'nullable', 'string', 'unique:visits,reference' ],
            'details' => [ 'required', 'array', 'min:1' ],
            'details.*.ticket_type_id' => [ 'required' ],
            'details.*.quantity' => [ 'required', 'integer', 'min:1' ],
        ] );

        $attributeName = [
            'user_id' => __( 'visit.user' ),
            'visit_date' => __( 'visit.visit_date' ),
            'reference' => __( 'visit.reference' ),
            'details' => __( 'visit.details' ),
            'details.*.ticket_type_id' => __( 'visit.ticket_type' ),
            'details.*.quantity' => __( 'visit.quantity' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        // Check if date is closed
        $dateClosure = DateClosure::where( 'date', $request->visit_date )
            ->where( 'status', 10 )
            ->first();

        if ( $dateClosure ) {
            $reason = $dateClosure->reason ? ' - ' . $dateClosure->reason : '';
            return response()->json( [
                'message' => __( 'visit.date_is_closed' ) . $reason,
            ], 422 );
        }

        // Check capacity availability
        $capacityService = new TicketCapacityService();
        $ticketsToCheck = [];

        foreach ( $request->details as $detail ) {
            $ticketsToCheck[] = [
                'ticket_type_id' => Helper::decode( $detail['ticket_type_id'] ),
                'quantity' => $detail['quantity'],
            ];
        }

        $capacityCheck = $capacityService->checkAvailability( $request->visit_date, $ticketsToCheck );

        if ( !$capacityCheck['available'] ) {
            $errorMessage = $capacityCheck['message'] . ':<br>';
            foreach ( $capacityCheck['details'] as $detail ) {
                if ( !$detail['is_available'] ) {
                    $errorMessage .= '- ' . $detail['ticket_type_name'] . ': ' . __( 'visit.requested' ) . ' ' . $detail['requested'] . ', ' . __( 'visit.available' ) . ' ' . $detail['available'] . '<br>';
                }
            }

            return response()->json( [
                'message' => rtrim( $errorMessage, '<br>' ),
            ], 422 );
        }

        DB::beginTransaction();

        try {
            // Auto-generate reference if not provided
            $reference = $request->reference ?? 'V-' . strtoupper( Str::random( 10 ) );

            $visit = UserVisit::create([
                'user_id' => $request->user_id,
                'visit_date' => $request->visit_date,
                'reference' => $reference,
                'status' => 10,
            ]);

            // Create visit details
            if ( $request->details && is_array( $request->details ) ) {
                foreach ( $request->details as $detail ) {
                    $ticketTypeId = Helper::decode( $detail['ticket_type_id'] );
                    $ticketType = TicketType::findOrFail( $ticketTypeId );
                    $totalPrice = $ticketType->price * $detail['quantity'];

                    VisitDetail::create([
                        'visit_id' => $visit->id,
                        'ticket_type_id' => $ticketTypeId,
                        'quantity' => $detail['quantity'],
                        'total_price' => $totalPrice,
                        'status' => 10,
                    ]);
                }
            }

            DB::commit();

            // Post visit to Turnstile after successful creation
            $encryptedId = Helper::encode( $visit->id );

            // Reload visit with user relationship
            $visit->load( 'user' );

            // Get QR data and prepare Turnstile payload
            $qrResult = self::getVisitQR( $encryptedId );

            // Post to Turnstile API
            if ( $qrResult['success'] && !empty( $qrResult['qr_codes'] ) ) {
                // Build tickets for API post
                $ticketsForApiPost = [];
                foreach ( $qrResult['qr_codes'] as $qr ) {
                    $ticketsForApiPost[] = [
                        'QRData' => $qr['qr_string'],
                        'OrderId' => $qr['order_id'],
                        'GuestType' => $qr['guest_type'],
                        'Seq' => $qr['sequence'],
                        'EntryDate' => $visit->visit_date->format( 'Y-m-d' ),
                        'UserName' => strtoupper( $visit->user ? ( $visit->user->first_name . ' ' . $visit->user->last_name ) : 'GUEST' ),
                        'UserEmail' => $visit->user ? $visit->user->email : '',
                        'UserContact' => $visit->user ? $visit->user->phone_number : '',
                    ];
                }

                $turnstileResponse = self::getTurnstileQR( 'PostQRData', [
                    'tickets_post' => $ticketsForApiPost,
                ]);

                if ( $turnstileResponse['success'] ) {
                    // Update visit to mark as recorded
                    $visit->update([ 'is_recorded' => 10 ]);
                } else {
                    // Log failure but don't fail the visit creation
                    \Log::warning( 'Turnstile QR posting failed for visit: ' . $visit->id, [
                        'visit_id' => $visit->id,
                        'reference' => $visit->reference,
                        'error' => $turnstileResponse['message'] ?? 'Unknown error'
                    ]);
                }
            }

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.visits' ) ) ] ),
            'data' => [
                'id' => Helper::encode( $visit->id ),
            ],
        ] );
    }

    public static function updateVisit( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'id' => [ 'required', 'exists:visits,id' ],
            'user_id' => [ 'nullable', 'exists:users,id' ],
            'visit_date' => [ 'required', 'date' ],
            'reference' => [ 'nullable', 'string', 'unique:visits,reference,' . $request->id ],
            'details' => [ 'required', 'array', 'min:1' ],
            'details.*.ticket_type_id' => [ 'required' ],
            'details.*.quantity' => [ 'required', 'integer', 'min:1' ],
        ] );

        $attributeName = [
            'user_id' => __( 'visit.user' ),
            'visit_date' => __( 'visit.visit_date' ),
            'reference' => __( 'visit.reference' ),
            'details' => __( 'visit.details' ),
            'details.*.ticket_type_id' => __( 'visit.ticket_type' ),
            'details.*.quantity' => __( 'visit.quantity' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        // Check if date is closed
        $dateClosure = DateClosure::where( 'date', $request->visit_date )
            ->where( 'status', 10 )
            ->first();

        if ( $dateClosure ) {
            $reason = $dateClosure->reason ? ' - ' . $dateClosure->reason : '';
            return response()->json( [
                'message' => __( 'visit.date_is_closed' ) . $reason,
            ], 422 );
        }

        // Check capacity availability (exclude current visit from count)
        $capacityService = new TicketCapacityService();
        $ticketsToCheck = [];

        foreach ( $request->details as $detail ) {
            $ticketsToCheck[] = [
                'ticket_type_id' => Helper::decode( $detail['ticket_type_id'] ),
                'quantity' => $detail['quantity'],
            ];
        }

        $capacityCheck = $capacityService->checkAvailability( $request->visit_date, $ticketsToCheck, $request->id );

        if ( !$capacityCheck['available'] ) {
            $errorMessage = $capacityCheck['message'] . ':<br>';
            foreach ( $capacityCheck['details'] as $detail ) {
                if ( !$detail['is_available'] ) {
                    $errorMessage .= '- ' . $detail['ticket_type_name'] . ': ' . __( 'visit.requested' ) . ' ' . $detail['requested'] . ', ' . __( 'visit.available' ) . ' ' . $detail['available'] . '<br>';
                }
            }

            return response()->json( [
                'message' => rtrim( $errorMessage, '<br>' ),
            ], 422 );
        }

        DB::beginTransaction();

        try {
            $visit = UserVisit::findOrFail( $request->id );

            $visit->update([
                'user_id' => $request->user_id,
                'visit_date' => $request->visit_date,
                'reference' => $request->reference,
            ]);

            // Delete existing visit details
            VisitDetail::where( 'visit_id', $visit->id )->delete();

            // Create new visit details
            if ( $request->details && is_array( $request->details ) ) {
                foreach ( $request->details as $detail ) {
                    $ticketTypeId = Helper::decode( $detail['ticket_type_id'] );
                    $ticketType = TicketType::findOrFail( $ticketTypeId );
                    $totalPrice = $ticketType->price * $detail['quantity'];

                    VisitDetail::create([
                        'visit_id' => $visit->id,
                        'ticket_type_id' => $ticketTypeId,
                        'quantity' => $detail['quantity'],
                        'total_price' => $totalPrice,
                        'status' => 10,
                    ]);
                }
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.visits' ) ) ] ),
        ] );
    }

    public static function deleteVisit( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'id' => [ 'required', 'exists:visits,id' ],
        ] );

        $validator->validate();

        DB::beginTransaction();

        try {
            $visit = UserVisit::findOrFail( $request->id );
            $visit->update([
                'status' => 20,
            ]);

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.visits' ) ) ] ),
        ] );
    }

    public static function updateStatus( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'id' => [ 'required', 'exists:visits,id' ],
            'status' => [ 'required', 'in:10,20' ],
        ] );

        $validator->validate();

        DB::beginTransaction();

        try {
            $visit = UserVisit::findOrFail( $request->id );
            $visit->update([
                'status' => $request->status,
            ]);

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.status_updated_successfully' ),
        ] );
    }

    public static function allVisits( $request ) {

        $visit = UserVisit::select( 'visits.*' )
            ->with( 'user:id,email' );

        $filterObject = self::filter( $request, $visit );
        $visit = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = strToUpper( $request->input( 'order.0.dir' ) );

            switch ( $request->input( 'order.0.column' ) ) {
                case '2':
                    $visit->orderBy( 'created_at', $dir );
                    break;
                case '3':
                    $visit->orderBy( 'visit_date', $dir );
                    break;
                case '4':
                    $visit->orderBy( 'reference', $dir );
                    break;
                case '5':
                    $visit->join( 'users', 'visits.user_id', '=', 'users.id', 'left' )
                          ->orderBy( 'users.email', $dir );
                    break;
                case '6':
                    $visit->orderBy( 'status', $dir );
                    break;
            }
        }

        $visitCount = $visit->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $visits = $visit->skip( $offset )->take( $limit )->get();

        if ( $visits ) {
            $visits->transform( function( $item ) {
                return [
                    'id' => Helper::encode( $item->id ),
                    'visit_date' => $item->visit_date ? $item->visit_date->format( 'Y-m-d' ) : '-',
                    'reference' => $item->reference ?? '-',
                    'email' => $item->user ? $item->user->email : '-',
                    'status' => $item->status,
                    'created_at' => $item->created_at->format( 'Y-m-d H:i:s' ),
                ];
            });
        }

        $totalRecord = UserVisit::count();

        $data = [
            'visits' => $visits,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $visitCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->created_date ) ) {
            if ( str_contains( $request->created_date, 'to' ) ) {
                $dates = explode( ' to ', $request->created_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );

                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'visits.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'visits.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->visit_date ) ) {
            if ( str_contains( $request->visit_date, 'to' ) ) {
                $dates = explode( ' to ', $request->visit_date );
                $model->whereBetween( 'visit_date', [ $dates[0], $dates[1] ] );
            } else {
                $model->where( 'visit_date', $request->visit_date );
            }
            $filter = true;
        }

        if ( !empty( $request->reference ) ) {
            $model->where( 'reference', 'LIKE', '%' . $request->reference . '%' );
            $filter = true;
        }

        if ( !empty( $request->user_name ) ) {
            $model->whereHas( 'user', function( $q ) use ( $request ) {
                $q->where( 'name', 'LIKE', '%' . $request->user_name . '%' );
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneVisit( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'id' => [ 'required', 'exists:visits,id' ],
        ] );

        $validator->validate();

        $visit = UserVisit::with( 'user:id,email', 'visitDetails.ticketType' )->findOrFail( $request->id );

        $details = [];
        if ( $visit->visitDetails ) {
            foreach ( $visit->visitDetails as $detail ) {
                $details[] = [
                    'ticket_type_id' => Helper::encode( $detail->ticket_type_id ),
                    'ticket_type_name' => $detail->ticketType ? $detail->ticketType->name : '-',
                    'ticket_type_price' => $detail->ticketType ? $detail->ticketType->price : 0,
                    'quantity' => $detail->quantity,
                    'total_price' => $detail->total_price,
                ];
            }
        }

        return response()->json( [
            'data' => [
                'id' => Helper::encode( $visit->id ),
                'user_id' => $visit->user_id ? Helper::encode( $visit->user_id ) : null,
                'email' => $visit->user ? $visit->user->email : null,
                'visit_date' => $visit->visit_date ? $visit->visit_date->format( 'Y-m-d' ) : null,
                'reference' => $visit->reference,
                'status' => $visit->status,
                'details' => $details,
                'user' => $visit->user,
            ],
        ] );
    }

    // User API Methods

    /**
     * Get user's visits
     */
    public static function getUserVisits( $request, $user ) {

        $visits = UserVisit::where( 'user_id', $user->id )
            ->with( 'visitDetails.ticketType' )
            ->orderBy( 'created_at', 'desc' );

        // Filter by status if provided
        if ( $request->has( 'status' ) && !empty( $request->status ) ) {
            $visits->where( 'status', $request->status );
        }

        $visits = $visits->get();

        $data = [];
        foreach ( $visits as $visit ) {
            $details = [];
            $totalAmount = 0;

            if ( $visit->visitDetails ) {
                foreach ( $visit->visitDetails as $detail ) {
                    $details[] = [
                        'ticket_type_id' => Helper::encode( $detail->ticket_type_id ),
                        'ticket_type_name' => $detail->ticketType ? $detail->ticketType->name : '-',
                        'ticket_type_price' => $detail->ticketType ? (float) $detail->ticketType->price : 0,
                        'quantity' => $detail->quantity,
                        'total_price' => (float) $detail->total_price,
                    ];
                    $totalAmount += (float) $detail->total_price;
                }
            }

            $data[] = [
                'id' => Helper::encode( $visit->id ),
                'visit_date' => $visit->visit_date ? $visit->visit_date->format( 'Y-m-d' ) : null,
                'reference' => $visit->reference,
                'status' => $visit->status,
                'status_text' => $visit->status == 10 ? 'Active' : 'Inactive',
                'total_amount' => $totalAmount,
                'created_at' => $visit->created_at->format( 'Y-m-d H:i:s' ),
                'details' => $details,
            ];
        }

        return $data;
    }

    /**
     * Create user visit
     */
    public static function createUserVisit( $request, $user ) {

        $validator = Validator::make( $request->all(), [
            'visit_date' => [ 'required', 'date' ],
            'details' => [ 'required', 'array', 'min:1' ],
            'details.*.ticket_type_id' => [ 'required' ],
            'details.*.quantity' => [ 'required', 'integer', 'min:1' ],
        ] );

        if ( $validator->fails() ) {
            return response()->json( [
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422 );
        }

        // Check if date is closed
        $dateClosure = DateClosure::where( 'date', $request->visit_date )
            ->where( 'status', 10 )
            ->first();

        if ( $dateClosure ) {
            $reason = $dateClosure->reason ? ' - ' . $dateClosure->reason : '';
            return response()->json( [
                'status' => 'error',
                'message' => __( 'visit.date_is_closed' ) . $reason,
            ], 422 );
        }

        // Check capacity availability
        $capacityService = new TicketCapacityService();
        $ticketsToCheck = [];

        foreach ( $request->details as $detail ) {
            $ticketsToCheck[] = [
                'ticket_type_id' => Helper::decode( $detail['ticket_type_id'] ),
                'quantity' => $detail['quantity'],
            ];
        }

        $capacityCheck = $capacityService->checkAvailability( $request->visit_date, $ticketsToCheck );

        if ( !$capacityCheck['available'] ) {
            $errorMessage = $capacityCheck['message'] . ':<br>';
            foreach ( $capacityCheck['details'] as $detail ) {
                if ( !$detail['is_available'] ) {
                    $errorMessage .= '- ' . $detail['ticket_type_name'] . ': ' . __( 'visit.requested' ) . ' ' . $detail['requested'] . ', ' . __( 'visit.available' ) . ' ' . $detail['available'] . '<br>';
                }
            }

            return response()->json( [
                'status' => 'error',
                'message' => rtrim( $errorMessage, '<br>' ),
            ], 422 );
        }

        DB::beginTransaction();

        try {
            // Auto-generate reference
            $reference = 'V-' . strtoupper( Str::random( 10 ) );

            $visit = UserVisit::create([
                'user_id' => $user->id,
                'visit_date' => $request->visit_date,
                'reference' => $reference,
                'status' => 10,
            ]);

            // Create visit details
            if ( $request->details && is_array( $request->details ) ) {
                foreach ( $request->details as $detail ) {
                    $ticketTypeId = Helper::decode( $detail['ticket_type_id'] );
                    $ticketType = TicketType::findOrFail( $ticketTypeId );
                    $totalPrice = $ticketType->price * $detail['quantity'];

                    VisitDetail::create([
                        'visit_id' => $visit->id,
                        'ticket_type_id' => $ticketTypeId,
                        'quantity' => $detail['quantity'],
                        'total_price' => $totalPrice,
                        'status' => 10,
                    ]);
                }
            }

            DB::commit();

            // Post visit to Turnstile after successful creation
            $encryptedId = Helper::encode( $visit->id );

            // Reload visit with user relationship
            $visit->load( 'user' );

            // Get QR data and prepare Turnstile payload (with image generation for API)
            $qrResult = self::getVisitQR( $encryptedId, null, true );

            // Prepare response data
            $responseData = [
                'id' => $encryptedId,
                'reference' => $visit->reference,
                'visit_date' => $visit->visit_date->format( 'Y-m-d' ),
            ];

            // Post to Turnstile API
            if ( $qrResult['success'] && !empty( $qrResult['qr_codes'] ) ) {
                // Build tickets for API post
                $ticketsForApiPost = [];
                foreach ( $qrResult['qr_codes'] as $qr ) {
                    $ticketsForApiPost[] = [
                        'QRData' => $qr['qr_string'],
                        'OrderId' => $qr['order_id'],
                        'GuestType' => $qr['guest_type'],
                        'Seq' => $qr['sequence'],
                        'EntryDate' => $visit->visit_date->format( 'Y-m-d' ),
                        'UserName' => strtoupper( $visit->user ? ( $visit->user->first_name . ' ' . $visit->user->last_name ) : 'GUEST' ),
                        'UserEmail' => $visit->user ? $visit->user->email : '',
                        'UserContact' => $visit->user ? $visit->user->phone_number : '',
                    ];
                }

                $turnstileResponse = self::getTurnstileQR( 'PostQRData', [
                    'tickets_post' => $ticketsForApiPost,
                ]);

                if ( $turnstileResponse['success'] ) {
                    // Update visit to mark as recorded
                    $visit->update([ 'is_recorded' => 10 ]);

                    $responseData['qr_codes'] = $qrResult['qr_codes'];
                    $responseData['total_tickets'] = count( $qrResult['qr_codes'] );
                    $responseData['turnstile_status'] = 'posted';
                } else {
                    // Log failure but don't fail the visit creation
                    \Log::warning( 'Turnstile QR posting failed for visit: ' . $visit->id, [
                        'visit_id' => $visit->id,
                        'reference' => $visit->reference,
                        'error' => $turnstileResponse['message'] ?? 'Unknown error'
                    ]);
                    $responseData['qr_codes'] = $qrResult['qr_codes'];
                    $responseData['total_tickets'] = count( $qrResult['qr_codes'] );
                    $responseData['turnstile_status'] = 'failed';
                    $responseData['turnstile_message'] = $turnstileResponse['message'] ?? 'Failed to post to Turnstile';
                }
            } else {
                $responseData['turnstile_status'] = 'failed';
                $responseData['turnstile_message'] = 'Failed to generate QR codes';
            }

            return $responseData;

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'status' => 'error',
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }
    }

    /**
     * Get user's single visit
     */
    public static function getUserVisit( $request, $user ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $visit = UserVisit::where( 'id', $request->id )
            ->where( 'user_id', $user->id )
            ->with( 'visitDetails.ticketType' )
            ->first();

        if ( !$visit ) {
            return null;
        }

        $details = [];
        $totalAmount = 0;

        if ( $visit->visitDetails ) {
            foreach ( $visit->visitDetails as $detail ) {
                $details[] = [
                    'ticket_type_id' => Helper::encode( $detail->ticket_type_id ),
                    'ticket_type_name' => $detail->ticketType ? $detail->ticketType->name : '-',
                    'ticket_type_price' => $detail->ticketType ? (float) $detail->ticketType->price : 0,
                    'quantity' => $detail->quantity,
                    'total_price' => (float) $detail->total_price,
                ];
                $totalAmount += (float) $detail->total_price;
            }
        }

        return [
            'id' => Helper::encode( $visit->id ),
            'visit_date' => $visit->visit_date ? $visit->visit_date->format( 'Y-m-d' ) : null,
            'reference' => $visit->reference,
            'status' => $visit->status,
            'status_text' => $visit->status == 10 ? 'Active' : 'Inactive',
            'total_amount' => $totalAmount,
            'created_at' => $visit->created_at->format( 'Y-m-d H:i:s' ),
            'details' => $details,
        ];
    }

    /**
     * Get availability for dates
     */
    public static function getAvailability( $request ) {

        $targetDate = $request->target_date ?? now()->format( 'Y-m' );

        // Check if it's a specific date (Y-m-d) or month (Y-m)
        $isSpecificDate = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $targetDate );
        $isMonth = preg_match( '/^\d{4}-\d{2}$/', $targetDate );

        if ( $isSpecificDate ) {
            // Return availability for a specific date
            return self::getDateAvailability( $targetDate );
        } elseif ( $isMonth ) {
            // Return availability for the entire month
            return self::getMonthAvailability( $targetDate );
        } else {
            return [
                'error' => 'Invalid date format. Use Y-m for month or Y-m-d for specific date.'
            ];
        }
    }

    /**
     * Get availability for a specific date
     */
    private static function getDateAvailability( $date ) {

        // Check if date is closed
        $dateClosure = DateClosure::where( 'date', $date )
            ->where( 'status', 10 )
            ->first();

        $isClosed = $dateClosure ? true : false;
        $closureReason = $dateClosure ? $dateClosure->reason : null;

        // Get all active ticket types
        $ticketTypes = TicketType::where( 'status', 10 )->get();

        $capacityService = new TicketCapacityService();
        $ticketAvailability = [];

        foreach ( $ticketTypes as $ticketType ) {
            $availabilityCheck = $capacityService->checkAvailability( $date, [[
                'ticket_type_id' => $ticketType->id,
                'quantity' => 1,
            ]] );

            $available = 0;
            $capacity = 0;
            $booked = 0;

            if ( isset( $availabilityCheck['details'][0] ) ) {
                $detail = $availabilityCheck['details'][0];
                $available = $detail['available'];
                $capacity = $detail['is_available'];
                $booked = $capacity - $available;
            }

            $nationality = $ticketType->nationality ?? 'other';

            if ( !isset( $ticketAvailability[$nationality] ) ) {
                $ticketAvailability[$nationality] = [];
            }

            $ticketAvailability[$nationality][] = [
                'ticket_type_id' => Helper::encode( $ticketType->id ),
                'ticket_type_name' => $ticketType->name,
                'price' => (float) $ticketType->price,
                'capacity' => $capacity,
                'booked' => $booked,
                'available' => $available,
                'is_available' => $available > 0 && !$isClosed,
            ];
        }

        return [
            'date' => $date,
            'is_closed' => $isClosed,
            'closure_reason' => $closureReason,
            'is_available' => !$isClosed,
            'ticket_types' => $ticketAvailability,
        ];
    }

    /**
     * Get availability for a month
     */
    private static function getMonthAvailability( $month ) {

        // Parse month (Y-m format)
        $dateParts = explode( '-', $month );
        $year = $dateParts[0];
        $monthNum = $dateParts[1];

        // Get first and last day of the month
        $tz = 'Asia/Kuala_Lumpur';

        $startDate = Carbon::now( $tz )->startOfDay();
        $endDate   = Carbon::now( $tz )->endOfMonth()->endOfDay();

        // Get closed dates for this month
        $closedDates = DateClosure::where( 'status', 10 )
            ->whereBetween( 'date', [ $startDate->format( 'Y-m-d' ), $endDate->format( 'Y-m-d' ) ] )
            ->get()
            ->keyBy( 'date' );

        // Get all active ticket types
        $ticketTypes = TicketType::where( 'status', 10 )->get();

        $capacityService = new TicketCapacityService();
        $datesAvailability = [];

        // Loop through each day of the month
        $currentDate = $startDate->copy();
        while ( $currentDate <= $endDate ) {
            $dateStr = $currentDate->format( 'Y-m-d' );
            $isClosed = isset( $closedDates[$dateStr] );
            $closureReason = $isClosed ? $closedDates[$dateStr]->reason : null;

            $ticketAvailability = [];
            $hasAvailability = false;

            foreach ( $ticketTypes as $ticketType ) {
                $availabilityCheck = $capacityService->checkAvailability( $dateStr, [[
                    'ticket_type_id' => $ticketType->id,
                    'quantity' => 1,
                ]] );

                $available = 0;
                $capacity = 0;
                $booked = 0;


                if ( isset( $availabilityCheck['details'][0] ) ) {
                    $detail = $availabilityCheck['details'][0];
                    $available = $detail['available'];
                    $capacity = $detail['is_available'];
                    $booked = $capacity - $available;

                }

                if ( $available > 0 && !$isClosed ) {
                    $hasAvailability = true;
                }

                $nationality = $ticketType->nationality ?? 'other';

                if ( !isset( $ticketAvailability[$nationality] ) ) {
                    $ticketAvailability[$nationality] = [];
                }

                $ticketAvailability[$nationality][] = [
                    'ticket_type_id' => Helper::encode( $ticketType->id ),
                    'ticket_type_name' => $ticketType->name,
                    'price' => (float) $ticketType->price,
                    'capacity' => $capacity,
                    'booked' => $booked,
                    'available' => $available,
                    'is_available' => $available > 0 && !$isClosed,
                ];
            }

            $datesAvailability[] = [
                'date' => $dateStr,
                'day_of_week' => $currentDate->format( 'l' ), // Monday, Tuesday, etc.
                'is_closed' => $isClosed,
                'closure_reason' => $closureReason,
                'is_available' => $hasAvailability,
                'ticket_types' => $ticketAvailability,
            ];

            $currentDate->addDay();
        }

        return [
            'month' => $month,
            'year' => (int) $year,
            'month_number' => (int) $monthNum,
            'month_name' => Carbon::create( $year, $monthNum, 1 )->format( 'F' ),
            'start_date' => $startDate->format( 'Y-m-d' ),
            'end_date' => $endDate->format( 'Y-m-d' ),
            'dates' => $datesAvailability,
        ];
    }

    /**
     * Get Turnstile QR Data
     *
     * @param string $command The command to send
     * @param array $data The data to post
     * @return mixed Response from Turnstile API
     */
    public static function getTurnstileQR( $command, $data = [] ) {

        // Get Turnstile configuration
        $config = config( 'services.turnstile' );
        $requestUrl = $config['request_url'] ?? null;
        $apiId = $config['api_id'] ?? null;
        $apiKey = $config['api_key'] ?? null;

        if ( !$requestUrl || !$apiId || !$apiKey ) {
            // Log configuration error
            ApiLog::create([
                'url' => $requestUrl ?? 'N/A',
                'method' => 'POST',
                'raw_response' => json_encode([
                    'error' => 'Turnstile configuration is incomplete',
                    'command' => $command,
                ]),
                'module_name' => 'Turnstile',
                'api_type' => 'QR Generation',
                'scope' => 'Error',
            ]);

            return [
                'success' => false,
                'message' => 'Turnstile configuration is incomplete',
            ];
        }

        // Prepare headers
        $headers = [
            'accept: */*',
            'accept-language: en-US,en;q=0.8',
            'content-type: application/json',
            'command: ' . $command,
            'ApiId: ' . $apiId,
            'ApiKey: ' . $apiKey,
        ];

        // Encode data as JSON
        $postData = json_encode( $data['tickets_post'] );
 
        // Make cURL POST request using Helper
        $response = Helper::curlPost( $requestUrl, $postData, $headers );

        if ( $response === false ) {
            // Log connection failure
            ApiLog::create([
                'url' => $requestUrl,
                'method' => 'POST',
                'raw_response' => json_encode([
                    'error' => 'Failed to connect to Turnstile API',
                    'command' => $command,
                    'request_data' => $data,
                ]),
                'module_name' => 'Turnstile',
                'api_type' => 'QR Generation',
                'scope' => 'Error',
            ]);

            return [
                'success' => false,
                'message' => 'Failed to connect to Turnstile API',
            ];
        }

        // Decode response
        $responseData = json_decode( $response, true );

        // Log successful API call
        ApiLog::create([
            'url' => $requestUrl,
            'method' => 'POST',
            'raw_response' => $response,
            'module_name' => 'Turnstile',
            'api_type' => 'QR Generation',
            'scope' => $command,
        ]);

        return [
            'success' => true,
            'data' => $responseData,
            'raw_response' => $response,
        ];
    }

    /**
     * Get Visit QR Code Data (for both admin and API)
     * Generates individual QR code for each ticket based on quantity
     * Makes a single API call with all tickets
     *
     * @param string $id Encrypted visit ID
     * @param User|null $user Optional user for access validation (API use)
     * @param bool $generateImages Whether to generate QR code images
     * @return array
     */
    public static function getVisitQR( $id, $user = null, $generateImages = false ) {

        $decodedId = Helper::decode( $id );

        // Build query
        $query = UserVisit::with( 'user:id,email,first_name,last_name', 'visitDetails.ticketType' )
            ->where( 'id', $decodedId );

        // If user is provided (API), ensure they own the visit
        if ( $user ) {
            $query->where( 'user_id', $user->id );
        }

        $visit = $query->first();

        if ( !$visit ) {
            return [
                'success' => false,
                'message' => 'Visit not found or access denied',
            ];
        }

        // Prepare visit details
        $details = [];
        $totalAmount = 0;
        $ticketsForApi = [];
        $ticketsForApiPost = [];
        $ticketCounter = 1;

        // Get current timestamp for QRData
        $now = now();
        $dateTimePrefix = $now->format( 'YmdHis' ); // YYYYMMDDHHMMSS

        // Pad order ID to 8 characters
        $orderId = str_pad( $visit->id, 8, '0', STR_PAD_LEFT );

        // Prepare user info
        $userName = 'GUEST';
        $userEmail = $visit->user ? $visit->user->email : '';
        $userContact = $visit->user ? $visit->user->phone_number : '';

        if ( $visit->visitDetails ) {
            foreach ( $visit->visitDetails as $detail ) {
                $details[] = [
                    'ticket_type_id' => $detail->ticket_type_id,
                    'ticket_type_name' => $detail->ticketType ? $detail->ticketType->name : '-',
                    'ticket_type_price' => $detail->ticketType ? (float) $detail->ticketType->price : 0,
                    'quantity' => $detail->quantity,
                    'total_price' => (float) $detail->total_price,
                ];
                $totalAmount += (float) $detail->total_price;

                // Determine guest type (ADULT or CHILD based on ticket type name)
                $ticketTypeName = $detail->ticketType ? strtoupper( $detail->ticketType->name ) : 'ADULT';
                $guestType = str_contains( $ticketTypeName, 'CHILD' ) ? 'CHILD' : 'ADULT';

                // Generate ticket data for each quantity
                for ( $i = 1; $i <= $detail->quantity; $i++ ) {
                    // Pad sequence to 3 characters
                    $sequence = str_pad( $ticketCounter, 3, '0', STR_PAD_LEFT );

                    // Build QRData: YYYYMMDDHHMMSS + OrderId(8) + GuestType + Seq(3) + "MOBILEAPP"
                    $qrData = $dateTimePrefix . $orderId . $guestType . $sequence . 'MOBILEAPP';

                    $ticketsForApi[] = [
                        'QRData' => $qrData,
                        'OrderId' => $orderId,
                        'GuestType' => $guestType,
                        'Seq' => $sequence,
                        'EntryDate' => $visit->visit_date ? $visit->visit_date->format( 'Y-m-d' ) : $now->format( 'Y-m-d' ),
                        'UserName' => strtoupper( $userName ),
                        'UserEmail' => $userEmail,
                        'UserContact' => $userContact,
                        'TicketNumber' => $ticketCounter,
                        'TicketTypeName' => $detail->ticketType ? $detail->ticketType->name : '-',
                        'TicketPrice' => $detail->ticketType ? (float) $detail->ticketType->price : 0,
                    ];

                    $ticketsForApiPost[] = [
                        'QRData' => $qrData,
                        'OrderId' => $orderId,
                        'GuestType' => $guestType,
                        'Seq' => $sequence,
                        'EntryDate' => $visit->visit_date ? $visit->visit_date->format( 'Y-m-d' ) : $now->format( 'Y-m-d' ),
                        'UserName' => strtoupper( $userName ),
                        'UserEmail' => $userEmail,
                        'UserContact' => $userContact,
                    ];

                    $ticketCounter++;
                }
            }
        }

        // Generate QR codes for display (no API call)
        $qrCodes = [];
        foreach ( $ticketsForApi as $ticket ) {
            $qrCode = [
                'ticket_number' => $ticket['TicketNumber'],
                'ticket_type_name' => $ticket['TicketTypeName'],
                'ticket_type_price' => $ticket['TicketPrice'],
                'qr_string' => $ticket['QRData'],
                'order_id' => $ticket['OrderId'],
                'guest_type' => $ticket['GuestType'],
                'sequence' => $ticket['Seq'],
            ];

            // Generate QR code image if requested
            if ( $generateImages ) {
                try {
                    $options = new \chillerlan\QRCode\QROptions([
                        'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG,
                        'eccLevel' => \chillerlan\QRCode\QRCode::ECC_H,
                        'scale' => 10,
                        'imageBase64' => false,
                    ]);

                    $qrcode = new \chillerlan\QRCode\QRCode($options);
                    $imageData = $qrcode->render($ticket['QRData']);

                    // Save to storage
                    $filename = 'qr_' . $visit->reference . '_' . str_pad($ticket['TicketNumber'], 3, '0', STR_PAD_LEFT) . '.png';
                    $path = 'qrcodes/' . $visit->id . '/' . $filename;

                    \Storage::disk('public')->put($path, $imageData);

                    // Return public URL
                    $qrCode['qr_image'] = asset('storage/' . $path);
                    $qrCode['qr_image_path'] = $path;
                } catch ( \Exception $e ) {
                    $qrCode['qr_image'] = null;
                    $qrCode['image_error'] = $e->getMessage();
                }
            }

            $qrCodes[] = $qrCode;
        }

        return [
            'success' => true,
            'visit_data' => [
                'id' => Helper::encode( $visit->id ),
                'reference' => $visit->reference,
                'visit_date' => $visit->visit_date ? $visit->visit_date->format( 'Y-m-d' ) : null,
                'user_email' => $visit->user ? $visit->user->email : null,
                'user_name' => $visit->user ? ( $visit->user->first_name . ' ' . $visit->user->last_name ) : null,
                'status' => $visit->status,
                'status_text' => $visit->status == 10 ? 'Active' : 'Inactive',
                'total_amount' => $totalAmount,
                'total_tickets' => count( $qrCodes ),
                'details' => $details,
            ],
            'qr_codes' => $qrCodes,
        ];
    }

    /**
     * Get ticket types (optionally filtered by nationality)
     */
    public static function getTicketTypes( $request ) {

        $query = TicketType::where( 'status', 10 );

        // Filter by nationality if provided
        if ( $request->has( 'nationality' ) && $request->nationality ) {
            $query->where( 'nationality', $request->nationality );
        }

        $ticketTypes = $query->get();

        // Group by nationality
        $groupedTicketTypes = [];
        foreach ( $ticketTypes as $ticketType ) {
            $nationality = $ticketType->nationality ?? 'other';

            if ( !isset( $groupedTicketTypes[$nationality] ) ) {
                $groupedTicketTypes[$nationality] = [];
            }

            $groupedTicketTypes[$nationality][] = [
                'id' => Helper::encode( $ticketType->id ),
                'name' => $ticketType->name,
                'nationality' => $ticketType->nationality,
                'price' => (float) $ticketType->price,
                'active' => $ticketType->active,
            ];
        }

        return $groupedTicketTypes;
    }
}
