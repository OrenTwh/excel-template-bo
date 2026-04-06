<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
    Storage,
};

use Helper;

use App\Models\{
    Property,
    PropertyLocation,
    Developer,
    DeveloperGallery,
    FileManager,
};

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class DeveloperService
{

    public static function createDeveloper( $request ) {

        $validator = Validator::make( $request->all(), [
            'name' => [ 'required' ],
            'company_registration_number' => [ 'nullable' ],
            'developer_type' => [ 'nullable' ],
            'logo' => [ 'nullable' ],
        ] );

        $attributeName = [
            'name' => __( 'developer.name' ),
            'company_registration_number' => __( 'developer.company_registration_number' ),
            'developer_type' => __( 'developer.developer_type' ),
            'logo' => __( 'developer.logo' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $developerCreate = Developer::create([
                'name' => $request->name,
                'company_registration_number' => $request->company_registration_number ? $request->company_registration_number : 1,
                'developer_type' => $request->developer_type,
            ]);

            $logo = explode( ',', $request->logo );

            $imageFiles = FileManager::whereIn( 'id', $logo )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'developer/' . $developerCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                    $developerCreate->logo = $target;
                    $developerCreate->save();

                    $imageFile->status = 10;
                    $imageFile->save();

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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.developers' ) ) ] ),
        ] );
    }
    
    public static function updateDeveloper( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'name' => [ 'required' ],
            'company_registration_number' => [ 'nullable' ],
            'developer_type' => [ 'nullable' ],
            'logo' => [ 'nullable' ],
        ] );

        $attributeName = [
            'name' => __( 'developer.name' ),
            'company_registration_number' => __( 'developer.company_registration_number' ),
            'developer_type' => __( 'developer.developer_type' ),
            'logo' => __( 'developer.logo' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();
        
        DB::beginTransaction();

        try {
            $updateDeveloper = Developer::find( $request->id );
    
            $updateDeveloper->name = $request->name;
            $updateDeveloper->developer_type = $request->developer_type ? $request->developer_type : 1;
            $updateDeveloper->company_registration_number = $request->company_registration_number;

            $logo = explode( ',', $request->logo );

            $imageFiles = FileManager::whereIn( 'id', $logo )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'developer/' . $updateDeveloper->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                    $updateDeveloper->logo = $target;
                    $updateDeveloper->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateDeveloper->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.developers' ) ) ] ),
        ] );
    }

    public static function allDevelopers( $request ) {

        $developers = Developer::select( 'developers.*');

        $filterObject = self::filter( $request, $developers );
        $developer = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case '2':
                    $developer->orderBy( 'developers.created_at', $dir );
                    break;
                case '4':
                    $developer->orderBy( 'developers.name', $dir );
                    break;
                case '5':
                    $developer->orderBy( 'developers.developer_type', $dir );
                    break;
                case '6':
                    $developer->orderBy( 'developers.status', $dir );
                    break;
            }
        }

            $developerCount = $developer->count();

            $limit = $request->length == -1 ? 1000000 : $request->length;
            $offset = $request->start;

            $developers = $developer->skip( $offset )->take( $limit )->get();

            if ( $developers ) {
                $developers->append( [
                    'encrypted_id',
                    'logo_path',
                ] );
            }

            $totalRecord = Developer::count();

            $data = [
                'developers' => $developers,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $developerCount : $totalRecord,
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

                $model->whereBetween( 'developers.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'developers.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->name ) ) {
            $model->where( 'developers.name', 'LIKE', '%' . $request->name . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'developers.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->developer_type)) {
            $model->where('developer_type', $request->developer_type);
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->voucher_type ) ) {
            $model->where( 'type', $request->voucher_type );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }

        if ( !empty( $request->code ) ) {
            $model->where( 'code', 'LIKE', '%' . $request->code . '%' );
            $filter = true;
        }

        if ( !empty( $request->vending_machine_id ) ) {
            $vendingMachineDevelopers = VendingMachineStock::where( 'vending_machine_id', $request->vending_machine_id )->pluck( 'developer_id' );
            $model->whereNotIn( 'id', $vendingMachineDevelopers );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneDeveloper( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $developer = Developer::find( $request->id );

        $developer->append( ['encrypted_id','logo_path',] );
        
        return response()->json( $developer );
    }

    public static function deleteDeveloper( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'developer.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            Developer::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.developers' ) ) ] ),
        ] );
    }

    public static function updateDeveloperStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateDeveloper = Developer::find( $request->id );
            $updateDeveloper->status = $updateDeveloper->status == 10 ? 20 : 10;

            $updateDeveloper->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'developer' => $updateDeveloper,
                    'message_key' => 'update_developer_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_developer_failed',
            ], 500 );
        }
    }

    public static function removeDeveloperGalleryImage( $request ) {

        $updateDeveloper = Developer::find( Helper::decode($request->id) );
        
        switch ($request->scope) {
            case 'image':
                Storage::delete( 'public/' . $updateDeveloper->logo );
                $updateDeveloper->logo = null;
                break;

            case 'unclaimed_image':
                Storage::delete( 'public/' . $updateDeveloper->unclaimed_image );
                $updateDeveloper->unclaimed_image = null;
                break;

            case 'claiming_image':
                Storage::delete( 'public/' . $updateDeveloper->claiming_image );
                $updateDeveloper->claiming_image = null;
                break;

            case 'claimed_image':
                Storage::delete( 'public/' . $updateDeveloper->claimed_image );
                $updateDeveloper->claimed_image = null;
                break;
            
            default:
                # code...
                break;
        }

        $updateDeveloper->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'developer.logo' ) ) ] ),
        ] );
    }

    public static function getDevelopers( $request )
    {

        $developers = Developer::with(['voucher'])
        ->where('status', 10)
        ->where(function ($query) {
            $query->where(function ($query) {
                $query->whereNull('start_date');
                $query->whereNull('expired_date');
            })
            ->orWhere(function ($query) {
                $query->where('start_date', '<=', now()->endOfDay());
                $query->where('expired_date', '>=', now()->startOfDay());
            });
        })
        ->when(auth()->check(), function ($query) use ( $request ) {

            if ( $request->show_claimed != 1 ) {  

                $user = auth()->user();
    
                // Exclude developers already viewed if `view_once` is enabled
                $query->whereNotIn('id', function ($subQuery) use ($user) {
                    $subQuery->select('developer_id')
                        ->from('developer_views') // Assuming a table tracks views
                        ->where('user_id', $user->id);
                });
        
                // Filter for new users if `new_user_only` is enabled
                if ($user->created_at->diffInDays(now()) > 7) { // Assuming "new user" means 7 days
                    $query->where('new_user_only', 0);
                }
            }

        })
        ->orderBy('created_at', 'DESC')
        ->get();

        $claimedDeveloperIds = DeveloperReward::where('user_id', auth()->user()->id)
        ->pluck('developer_id')
        ->toArray();

        $developers = $developers->map(function ($developer) use ( $claimedDeveloperIds ) {
            $developer->claimed = in_array($developer->id, $claimedDeveloperIds) ? 'claimed' : 'unclaim';
            $developer->makeHidden( [ 'created_at', 'updated_at'] );
            $developer->append([ 'image_path', 'unclaimed_image_path', 'claiming_image_path', 'claimed_image_path' ]);
            $developer->voucher?->append(['decoded_adjustment', 'image_path','voucher_type','voucher_type_label']);
            return $developer;
        });

        return response()->json( [
            'message' => '',
            'message_key' => 'get_developer_success',
            'data' => $developers,
        ] );

    }

    private static function sendNotification( $user, $key, $message ) {

        $messageContent = array();

        $messageContent['key'] = $key;
        $messageContent['id'] = $user->id;
        $messageContent['message'] = $message;

        Helper::sendNotification( $affiliate->user_id, $messageContent );
        
    }

}