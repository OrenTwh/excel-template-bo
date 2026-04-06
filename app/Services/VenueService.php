<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
    Storage,
};
use PhpOffice\PhpSpreadsheet\IOFactory;

use Helper;

use App\Models\{
    Venue,
    VenueSport,
    Sport,
    SportsTag,
    Court,
};

use Carbon\Carbon;

class VenueService
{
    // ─── API Methods ──────────────────────────────────────────────────────────

    public static function getVenues($request)
    {
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $sortByDistance = is_numeric($lat) && is_numeric($lng);

        $query = Venue::where('status', 10)
            ->with(['sports:id,name,slug,icon']);

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('address')) {
            $query->where('address_1', 'like', '%' . $request->address . '%');
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        if ($request->filled('state')) {
            $query->where('state', 'like', '%' . $request->state . '%');
        }

        if ($request->filled('postcode')) {
            $query->where('postcode', 'like', '%' . $request->postcode . '%');
        }

        $sportIds = [];
        if ($request->filled('sport_ids')) {
            $sportIds = array_filter(array_map(
                fn($id) => Helper::decode($id),
                (array) $request->sport_ids
            ));
        } elseif ($request->filled('sport_id')) {
            $sportIds = [Helper::decode($request->sport_id)];
        }

        if (!empty($sportIds)) {
            $query->whereHas('sports', fn($q) => $q->whereIn('sports.id', $sportIds));
        }

        if ($sortByDistance) {
            $query->selectRaw(
                'venues.*, ROUND( 6371 * acos( cos( radians(?) ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(?) ) + sin( radians(?) ) * sin( radians(latitude) ) ), 2 ) AS distance',
                [(float) $lat, (float) $lng, (float) $lat]
            )->orderBy('distance');
        } else {
            $query->orderBy('name');
        }

        $perPage = $request->input('per_page', 20);
        $venues  = $query->paginate($perPage);

        $venues->getCollection()->each(function ($venue) use ($sortByDistance) {
            $venue->append(['encrypted_id', 'image_path']);
            $venue->sports->each(fn($s) => $s->append(['encrypted_id', 'icon_path']));
            if (!$sortByDistance) {
                $venue->makeHidden('distance');
            }
        });

        return $venues;
    }

    public static function getOneVenue($request)
    {
        $id    = Helper::decode($request->id);
        $venue = Venue::where('status', 10)
            ->with(['sports:id,name,slug,icon'])
            ->find($id);

        if ($venue) {
            $venue->append(['encrypted_id', 'image_path']);
            $venue->sports->each(fn($s) => $s->append(['encrypted_id', 'icon_path']));
        }

        return $venue;
    }

    public static function getVenueSports($request)
    {
        $venueId = Helper::decode($request->venue_id);

        $query = VenueSport::where('venue_id', $venueId)
            ->where('status', 10)
            ->with([
                'sport:id,name,slug,icon',
                'courts' => fn($q) => $q->where('status', 10)->select('id', 'venue_sport_id', 'name', 'slug', 'capacity', 'image'),
            ]);

        if ($request->filled('sport_id')) {
            $query->where('sport_id', Helper::decode($request->sport_id));
        }

        $venueSports = $query->get();

        $venueSports->each(function ($vs) {
            $vs->append('encrypted_id');
            $vs->sport?->append(['encrypted_id', 'icon_path']);
            $vs->courts->each(fn($c) => $c->append(['encrypted_id', 'image_path']));
        });

        return $venueSports;
    }

    // ─── Venues ──────────────────────────────────────────────────────────────

    public static function allVenues( $request )
    {
        $venues = Venue::withCount( 'venueSports' )
            ->select( 'venues.*' );

        $filterObject = self::filter( $request, $venues );
        $venues = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1: $venues->orderBy( 'name', $dir ); break;
                case 2: $venues->orderBy( 'city', $dir ); break;
                case 3: $venues->orderBy( 'status', $dir ); break;
            }
        }

        $total  = Venue::count();
        $count  = $venues->count();
        $limit  = $request->length == -1 ? 1000000 : $request->length;
        $result = $venues->skip( $request->start )->take( $limit )->get();

        $result->append( [ 'encrypted_id' ] );

        return response()->json( [
            'venues'          => $result,
            'draw'            => $request->draw,
            'recordsFiltered' => $filter ? $count : $total,
            'recordsTotal'    => $total,
        ] );
    }

    public static function oneVenue( $request )
    {
        $venue = Venue::with( 'venueSports.sport' )
            ->find( Helper::decode( $request->id ) );

        // Resolve amenity IDs to name/id for Select2 pre-population
        $amenityIds = $venue->amenities ?? [];
        $venue->amenity_details = SportsTag::whereIn( 'id', $amenityIds )
            ->get( [ 'id', 'name' ] )
            ->toArray();

        return response()->json( $venue );
    }

    public static function createVenue( $request )
    {
        $validator = Validator::make( $request->all(), [
            'name'          => [ 'required', 'string', 'max:255' ],
            'slug'          => [ 'required', 'string', 'max:255', 'unique:venues,slug' ],
            'description'   => [ 'nullable', 'string' ],
            'about_us'      => [ 'nullable', 'string' ],
            'amenities'     => [ 'nullable', 'array' ],
            'amenities.*'   => [ 'integer', 'exists:sports_tags,id' ],
            'opening_hours'         => [ 'nullable', 'string' ],
            'opening_hours_pricing' => [ 'nullable', 'string' ],
            'venue_layout'          => [ 'nullable', 'mimes:jpeg,jpg,png', 'max:5120' ],
            'venue_policy'          => [ 'nullable', 'string' ],
            'gmap_link'             => [ 'nullable', 'string', 'max:500' ],
            'waze_link'             => [ 'nullable', 'string', 'max:500' ],
            'calling_code'          => [ 'nullable', 'string', 'max:10' ],
            'phone_number'          => [ 'nullable', 'string', 'max:20' ],
            'whatsapp_link'         => [ 'nullable', 'string', 'max:500' ],
            'address_1'             => [ 'nullable', 'string', 'max:255' ],
            'address_2'             => [ 'nullable', 'string', 'max:255' ],
            'city'                  => [ 'nullable', 'string', 'max:100' ],
            'state'                 => [ 'nullable', 'string', 'max:100' ],
            'postcode'              => [ 'nullable', 'string', 'max:10' ],
            'latitude'              => [ 'nullable', 'numeric', 'between:-90,90' ],
            'longitude'             => [ 'nullable', 'numeric', 'between:-180,180' ],
            'image'                 => [ 'nullable', 'mimes:jpeg,jpg,png', 'max:2048' ],
        ] );

        $validator->validate();

        DB::beginTransaction();

        try {
            $data = $request->only( [
                'name', 'slug', 'description', 'about_us', 'opening_hours_pricing', 'venue_policy',
                'gmap_link', 'waze_link', 'calling_code', 'phone_number', 'whatsapp_link',
                'address_1', 'address_2', 'city', 'state', 'postcode', 'latitude', 'longitude',
            ] );
            $data['status']    = 10;
            $data['amenities'] = $request->input( 'amenities', [] );

            // opening_hours is sent as a JSON string from the form
            $oh = $request->input( 'opening_hours' );
            $data['opening_hours'] = $oh ? json_decode( $oh, true ) : null;

            if ( $request->hasFile( 'image' ) ) {
                $data['image'] = $request->file( 'image' )->store( 'venues/images', [ 'disk' => 'public' ] );
            }

            if ( $request->hasFile( 'venue_layout' ) ) {
                $data['venue_layout'] = $request->file( 'venue_layout' )->store( 'venues/layouts', [ 'disk' => 'public' ] );
            }

            $venue = Venue::create( $data );

            DB::commit();
        } catch ( \Throwable $th ) {
            DB::rollback();
            return response()->json( [ 'message' => $th->getMessage() . ' line: ' . $th->getLine() ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => 'Venue' ] ),
            'data'    => [ 'id' => $venue->id, 'encrypted_id' => $venue->encrypted_id ],
        ] );
    }

    public static function updateVenue( $request )
    {
        $request->merge( [ 'id' => Helper::decode( $request->id ) ] );

        $validator = Validator::make( $request->all(), [
            'id'            => [ 'required', 'exists:venues,id' ],
            'name'          => [ 'required', 'string', 'max:255' ],
            'slug'          => [ 'required', 'string', 'max:255', 'unique:venues,slug,' . $request->id ],
            'description'   => [ 'nullable', 'string' ],
            'about_us'      => [ 'nullable', 'string' ],
            'amenities'     => [ 'nullable', 'array' ],
            'amenities.*'   => [ 'integer', 'exists:sports_tags,id' ],
            'opening_hours'         => [ 'nullable', 'string' ],
            'opening_hours_pricing' => [ 'nullable', 'string' ],
            'venue_layout'          => [ 'nullable', 'mimes:jpeg,jpg,png', 'max:5120' ],
            'venue_policy'          => [ 'nullable', 'string' ],
            'gmap_link'             => [ 'nullable', 'string', 'max:500' ],
            'waze_link'             => [ 'nullable', 'string', 'max:500' ],
            'calling_code'          => [ 'nullable', 'string', 'max:10' ],
            'phone_number'          => [ 'nullable', 'string', 'max:20' ],
            'whatsapp_link'         => [ 'nullable', 'string', 'max:500' ],
            'address_1'             => [ 'nullable', 'string', 'max:255' ],
            'address_2'             => [ 'nullable', 'string', 'max:255' ],
            'city'                  => [ 'nullable', 'string', 'max:100' ],
            'state'                 => [ 'nullable', 'string', 'max:100' ],
            'postcode'              => [ 'nullable', 'string', 'max:10' ],
            'latitude'              => [ 'nullable', 'numeric', 'between:-90,90' ],
            'longitude'             => [ 'nullable', 'numeric', 'between:-180,180' ],
            'image'                 => [ 'nullable', 'mimes:jpeg,jpg,png', 'max:2048' ],
        ] );

        $validator->validate();

        DB::beginTransaction();

        try {
            $venue = Venue::find( $request->id );

            $venue->fill( $request->only( [
                'name', 'slug', 'description', 'about_us', 'opening_hours_pricing', 'venue_policy',
                'gmap_link', 'waze_link', 'calling_code', 'phone_number', 'whatsapp_link',
                'address_1', 'address_2', 'city', 'state', 'postcode', 'latitude', 'longitude',
            ] ) );

            $venue->amenities = $request->input( 'amenities', [] );

            $oh = $request->input( 'opening_hours' );
            $venue->opening_hours = $oh ? json_decode( $oh, true ) : null;

            if ( $request->hasFile( 'image' ) ) {
                if ( $venue->image ) {
                    Storage::disk( 'public' )->delete( $venue->image );
                }
                $venue->image = $request->file( 'image' )->store( 'venues/images', [ 'disk' => 'public' ] );
            }

            if ( $request->hasFile( 'venue_layout' ) ) {
                if ( $venue->venue_layout ) {
                    Storage::disk( 'public' )->delete( $venue->venue_layout );
                }
                $venue->venue_layout = $request->file( 'venue_layout' )->store( 'venues/layouts', [ 'disk' => 'public' ] );
            }

            $venue->save();

            DB::commit();
        } catch ( \Throwable $th ) {
            DB::rollback();
            return response()->json( [ 'message' => $th->getMessage() . ' line: ' . $th->getLine() ], 500 );
        }

        return response()->json( [ 'message' => __( 'template.x_updated', [ 'title' => 'Venue' ] ) ] );
    }

    public static function updateVenueStatus( $request )
    {
        $request->merge( [ 'id' => Helper::decode( $request->id ) ] );

        $venue         = Venue::find( $request->id );
        $venue->status = $request->status;
        $venue->save();

        return response()->json( [ 'message' => __( 'template.x_updated', [ 'title' => 'Venue' ] ) ] );
    }

    // ─── Venue Sports ─────────────────────────────────────────────────────────

    public static function allVenueSportsGlobal( $request )
    {
        $venueSports = VenueSport::with( [ 'venue', 'sport' ] )
            ->withCount( 'courts' )
            ->select( 'venue_sports.*' );

        $filter = false;

        if ( !empty( $request->venue ) ) {
            $venueSports->whereHas( 'venue', function ( $query ) use ( $request ) {
                $query->where( 'name', 'LIKE', '%' . $request->venue . '%' );
            } );
            $filter = true;
        }

        if ( !empty( $request->sport ) ) {
            $venueSports->whereHas( 'sport', function ( $query ) use ( $request ) {
                $query->where( 'name', 'LIKE', '%' . $request->sport . '%' );
            } );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $venueSports->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->created_date ) ) {
            $dates = explode( ' to ', $request->created_date );
            if ( count( $dates ) == 2 ) {
                $venueSports->whereBetween( DB::raw( 'DATE(created_at)' ), [ trim( $dates[0] ), trim( $dates[1] ) ] );
            } else {
                $venueSports->whereDate( 'created_at', trim( $dates[0] ) );
            }
            $filter = true;
        }

        $total  = VenueSport::count();
        $count  = $venueSports->count();
        $limit  = $request->length == -1 ? 1000000 : $request->length;
        $result = $venueSports->orderBy( 'venue_sports.created_at', 'desc' )->skip( $request->start )->take( $limit )->get();

        $result->append( [ 'encrypted_id' ] );

        return response()->json( [
            'venue_sports'    => $result,
            'draw'            => $request->draw,
            'recordsFiltered' => $filter ? $count : $total,
            'recordsTotal'    => $total,
        ] );
    }

    public static function oneVenueSport( $request )
    {
        $venueSport = VenueSport::with( [ 'venue', 'sport' ] )
            ->find( Helper::decode( $request->id ) );

        return response()->json( $venueSport );
    }

    public static function allVenueSports( $request )
    {
        $request->merge( [ 'venue_id' => Helper::decode( $request->venue_id ) ] );

        $venueSports = VenueSport::with( 'sport' )
            ->withCount( 'courts' )
            ->where( 'venue_id', $request->venue_id )
            ->get()
            ->append( [ 'encrypted_id' ] );

        return response()->json( [ 'venue_sports' => $venueSports ] );
    }

    public static function importVenueSports( $request )
    {
        $validator = Validator::make( $request->all(), [
            'file' => [ 'required', 'file', 'mimes:xlsx,xls', 'max:5120' ],
        ] );

        $validator->validate();

        try {
            $spreadsheet = IOFactory::load( $request->file( 'file' )->getRealPath() );
        } catch ( \Throwable $th ) {
            return response()->json( [ 'message' => 'Could not read file: ' . $th->getMessage() ], 422 );
        }

        $vsDefaults = [
            'slot_duration'    => null,
            'price_per_slot'   => null,
            'price_per_person' => null,
            'price_per_night'  => null,
            'open_time'      => '08:00:00',
            'close_time'     => '22:00:00',
            'operating_days' => [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ],
            'status'         => 10,
        ];

        $venuesCreated  = 0;
        $sportsCreated  = 0;
        $vsCreated      = 0;
        $vsSkipped      = 0;
        $courtsCreated  = 0;
        $courtsSkipped  = 0;
        $virtualCreated = 0;
        $resolvedVsIds  = []; // tracks every VenueSport touched during import

        // ── Helpers ───────────────────────────────────────────────────────────

        $resolveVenue = function( $name ) use ( &$venuesCreated ) {
            $name  = trim( $name ?? '' );
            $venue = Venue::whereRaw( 'LOWER(name) = ?', [ strtolower( $name ) ] )->first();
            if ( !$venue ) {
                $venue = Venue::create( [ 'name' => $name, 'slug' => Str::slug( $name ), 'status' => 10 ] );
                $venuesCreated++;
            }
            return $venue;
        };

        $resolveSport = function( $name ) use ( &$sportsCreated ) {
            $name  = trim( $name ?? '' );
            $sport = Sport::whereRaw( 'LOWER(name) = ?', [ strtolower( $name ) ] )->first();
            if ( !$sport ) {
                $sport = Sport::create( [ 'name' => $name, 'slug' => Str::slug( $name ), 'status' => 10 ] );
                $sportsCreated++;
            }
            return $sport;
        };

        $resolveVenueSport = function( $venue, $sport ) use ( &$vsCreated, &$vsSkipped, &$resolvedVsIds, $vsDefaults ) {
            $vs = VenueSport::where( 'venue_id', $venue->id )->where( 'sport_id', $sport->id )->first();
            if ( !$vs ) {
                $vs = VenueSport::create( array_merge( $vsDefaults, [
                    'venue_id' => $venue->id,
                    'sport_id' => $sport->id,
                ] ) );
                $vsCreated++;
            } else {
                $vsSkipped++;
            }
            $resolvedVsIds[ $vs->id ] = $vs; // keyed so no duplicates
            return $vs;
        };

        DB::beginTransaction();

        try {

            // ── Sheet 1: tick-based (sport ✅ at venue) ───────────────────────
            // Row 0 = title row, Row 1 = header (Activities | Venue A | Venue B …)
            // Row 2+ = data
            $sheet1   = $spreadsheet->getSheet( 0 )->toArray( null, true, true, false );
            $header1  = array_values( $sheet1[1] ?? [] );
            $venues1  = array_slice( $header1, 1 );
            $venueMap = [];
            foreach ( $venues1 as $col => $vName ) {
                if ( empty( trim( $vName ?? '' ) ) ) continue;
                $venueMap[ $col ] = $resolveVenue( $vName );
            }

            $knownTicks = [ '✅', '✓', '✔', '1', 'true', 'yes', 'y', 'x' ];

            foreach ( array_slice( $sheet1, 2 ) as $row ) {
                $r         = array_values( $row );
                $sportName = trim( $r[0] ?? '' );
                if ( empty( $sportName ) ) continue;

                $sport = $resolveSport( $sportName );

                foreach ( $venueMap as $col => $venue ) {
                    $cellStr = trim( (string) ( $r[ $col + 1 ] ?? '' ) );
                    $isTick  = !empty( $cellStr )
                        && ( in_array( $cellStr, $knownTicks, true ) || in_array( strtolower( $cellStr ), $knownTicks, true ) )
                        && !in_array( strtolower( $cellStr ), [ '0', 'false', 'no', '-' ], true );

                    if ( $isTick ) $resolveVenueSport( $venue, $sport );
                }
            }

            // ── Sheet 2: count-based (sport → N bays/courts at venue) ─────────
            // Row 0 = header (Facilities | Venue A | Venue B …), Row 1+ = data
            if ( $spreadsheet->getSheetCount() >= 2 ) {
                $sheet2   = $spreadsheet->getSheet( 1 )->toArray( null, true, true, false );
                $header2  = array_values( $sheet2[1] ?? [] );
                $venues2  = array_slice( $header2, 1 );
                $venueMap2 = [];
                foreach ( $venues2 as $col => $vName ) {
                    if ( empty( trim( $vName ?? '' ) ) ) continue;
                    $venueMap2[ $col ] = $resolveVenue( $vName );
                }

                foreach ( array_slice( $sheet2, 1 ) as $row ) {
                    $r         = array_values( $row );
                    $sportName = trim( $r[0] ?? '' );
                    if ( empty( $sportName ) ) continue;

                    $sport = $resolveSport( $sportName );

                    foreach ( $venueMap2 as $col => $venue ) {
                        $cellStr = trim( (string) ( $r[ $col + 1 ] ?? '' ) );
                        if ( empty( $cellStr ) ) continue;

                        // Parse count — e.g. "44 bays" → 44
                        preg_match( '/(\d+)/', $cellStr, $numMatch );
                        $count = (int) ( $numMatch[1] ?? 0 );
                        if ( $count <= 0 ) continue;

                        // Parse unit word — e.g. "bays" → "Bay", fallback "Court"
                        preg_match( '/[a-zA-Z]+/', $cellStr, $wordMatch );
                        $unit = isset( $wordMatch[0] )
                            ? ucfirst( rtrim( strtolower( $wordMatch[0] ), 's' ) )
                            : 'Court';

                        $vs = $resolveVenueSport( $venue, $sport );

                        for ( $i = 1; $i <= $count; $i++ ) {
                            $courtName = $unit . ' ' . $i;
                            $courtSlug = Str::slug( $venue->slug . '-' . $courtName );

                            if ( Court::where( 'slug', $courtSlug )->exists() ) {
                                $courtsSkipped++;
                                continue;
                            }

                            Court::create( [
                                'venue_sport_id' => $vs->id,
                                'name'           => $courtName,
                                'slug'           => $courtSlug,
                                'capacity'       => 1,
                                'price_per_hour' => 0,
                                'status'         => 10,
                            ] );

                            $courtsCreated++;
                        }
                    }
                }
            }

            // ── Virtual courts for activity-based sports (no physical courts) ──
            // Any VenueSport touched during import that still has no courts gets
            // one virtual court named after the sport (e.g. "Flying Fox").
            foreach ( $resolvedVsIds as $vs ) {
                if ( Court::where( 'venue_sport_id', $vs->id )->exists() ) continue;

                $vs->loadMissing( [ 'venue', 'sport' ] );
                $courtName = $vs->sport->name ?? 'Activity';
                $courtSlug = Str::slug( ( $vs->venue->slug ?? 'venue' ) . '-' . $courtName );

                // Ensure slug uniqueness
                $base = $courtSlug;
                $n    = 1;
                while ( Court::where( 'slug', $courtSlug )->exists() ) {
                    $courtSlug = $base . '-' . $n++;
                }

                Court::create( [
                    'venue_sport_id' => $vs->id,
                    'name'           => $courtName,
                    'slug'           => $courtSlug,
                    'capacity'       => 1,
                    'price_per_hour' => 0,
                    'status'         => 10,
                ] );

                $virtualCreated++;
            }

            DB::commit();

        } catch ( \Throwable $th ) {
            DB::rollback();
            return response()->json( [ 'message' => $th->getMessage() . ' line: ' . $th->getLine() ], 500 );
        }

        $parts = [];
        $parts[] = "{$vsCreated} venue sport" . ( $vsCreated !== 1 ? 's' : '' ) . ' created';
        if ( $vsSkipped )     $parts[] = "{$vsSkipped} already existed (skipped)";
        if ( $sportsCreated ) $parts[] = "{$sportsCreated} new sport" . ( $sportsCreated !== 1 ? 's' : '' ) . ' added';
        if ( $venuesCreated ) $parts[] = "{$venuesCreated} new venue" . ( $venuesCreated !== 1 ? 's' : '' ) . ' created';
        if ( $courtsCreated )  $parts[] = "{$courtsCreated} court" . ( $courtsCreated !== 1 ? 's' : '' ) . ' created';
        if ( $courtsSkipped )  $parts[] = "{$courtsSkipped} court" . ( $courtsSkipped !== 1 ? 's' : '' ) . ' already existed (skipped)';
        if ( $virtualCreated ) $parts[] = "{$virtualCreated} virtual court" . ( $virtualCreated !== 1 ? 's' : '' ) . ' created for activity-based sports';

        return response()->json( [ 'message' => implode( ', ', $parts ) . '.' ] );
    }

    public static function addVenueSport( $request )
    {
        $request->merge( [ 'venue_id' => Helper::decode( $request->venue_id ) ] );

        $pmValues = implode( ',', array_keys( Sport::pricingMethodOptions() ) );

        $validator = Validator::make( $request->all(), [
            'venue_id'         => [ 'required', 'exists:venues,id' ],
            'sport_ids'        => [ 'required', 'array', 'min:1' ],
            'sport_ids.*'      => [ 'required', 'exists:sports,id', 'distinct' ],
            'pricing_method'   => [ 'required', 'string', 'in:' . $pmValues ],
            'slot_duration'    => [ 'nullable', 'integer', 'min:15' ],
            'price_per_slot'   => [ 'nullable', 'numeric', 'min:0' ],
            'price_per_person' => [ 'nullable', 'numeric', 'min:0' ],
            'price_per_night'  => [ 'nullable', 'numeric', 'min:0' ],
            'open_time'        => [ 'required', 'date_format:H:i' ],
            'close_time'       => [ 'required', 'date_format:H:i', 'after:open_time' ],
            'operating_days'   => [ 'required', 'array', 'min:1' ],
            'operating_days.*' => [ 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday' ],
        ] );

        $validator->validate();

        // Check for sports already assigned to this venue
        $duplicate = VenueSport::where( 'venue_id', $request->venue_id )
            ->whereIn( 'sport_id', $request->sport_ids )
            ->with( 'sport' )
            ->get();

        if ( $duplicate->isNotEmpty() ) {
            $names = $duplicate->map( fn( $vs ) => $vs->sport?->name ?? "ID {$vs->sport_id}" )->join( ', ' );
            return response()->json( [
                'errors' => [ 'sport_ids' => "The following sports are already configured for this venue: {$names}." ],
            ], 422 );
        }

        DB::beginTransaction();

        try {
            foreach ( $request->sport_ids as $sportId ) {
                VenueSport::create( [
                    'venue_id'         => $request->venue_id,
                    'sport_id'         => $sportId,
                    'pricing_method'   => $request->pricing_method,
                    'slot_duration'    => $request->slot_duration ?: null,
                    'price_per_slot'   => $request->price_per_slot !== '' ? $request->price_per_slot : null,
                    'price_per_person' => $request->price_per_person !== '' ? $request->price_per_person : null,
                    'price_per_night'  => $request->price_per_night !== '' ? $request->price_per_night : null,
                    'open_time'        => $request->open_time . ':00',
                    'close_time'       => $request->close_time . ':00',
                    'operating_days'   => $request->operating_days,
                    'status'           => 10,
                ] );
            }

            DB::commit();
        } catch ( \Throwable $th ) {
            DB::rollback();
            return response()->json( [ 'message' => $th->getMessage() . ' line: ' . $th->getLine() ], 500 );
        }

        $count = count( $request->sport_ids );

        return response()->json( [
            'message' => $count === 1
                ? __( 'template.new_x_created', [ 'title' => 'Sport' ] )
                : "{$count} sports added to venue successfully.",
        ] );
    }

    public static function updateVenueSport( $request )
    {
        $request->merge( [ 'id' => Helper::decode( $request->id ) ] );

        $venueSport = VenueSport::find( $request->id );

        $pmValues = implode( ',', array_keys( Sport::pricingMethodOptions() ) );

        $validator = Validator::make( $request->all(), [
            'pricing_method'   => [ 'required', 'string', 'in:' . $pmValues ],
            'slot_duration'    => [ 'nullable', 'integer', 'min:15' ],
            'price_per_slot'   => [ 'nullable', 'numeric', 'min:0' ],
            'price_per_person' => [ 'nullable', 'numeric', 'min:0' ],
            'price_per_night'  => [ 'nullable', 'numeric', 'min:0' ],
            'open_time'        => [ 'required', 'date_format:H:i' ],
            'close_time'       => [ 'required', 'date_format:H:i', 'after:open_time' ],
            'operating_days'   => [ 'required', 'array', 'min:1' ],
            'operating_days.*' => [ 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday' ],
        ] );

        $validator->validate();

        $venueSport->update( [
            'pricing_method'   => $request->pricing_method,
            'slot_duration'    => $request->slot_duration ?: null,
            'price_per_slot'   => $request->price_per_slot !== '' ? $request->price_per_slot : null,
            'price_per_person' => $request->price_per_person !== '' ? $request->price_per_person : null,
            'price_per_night'  => $request->price_per_night !== '' ? $request->price_per_night : null,
            'open_time'        => $request->open_time . ':00',
            'close_time'       => $request->close_time . ':00',
            'operating_days'   => $request->operating_days,
        ] );

        return response()->json( [ 'message' => __( 'template.x_updated', [ 'title' => 'Sport' ] ) ] );
    }

    public static function updateVenueSportStatus( $request )
    {
        $request->merge( [ 'id' => Helper::decode( $request->id ) ] );

        $venueSport         = VenueSport::find( $request->id );
        $venueSport->status = $request->status;
        $venueSport->save();

        return response()->json( [ 'message' => __( 'template.x_updated', [ 'title' => 'Venue Sport' ] ) ] );
    }

    public static function removeVenueSport( $request )
    {
        $request->merge( [ 'id' => Helper::decode( $request->id ) ] );

        $venueSport = VenueSport::find( $request->id );

        if ( !$venueSport ) {
            return response()->json( [ 'message' => 'Not found' ], 404 );
        }

        $venueSport->delete();

        return response()->json( [ 'message' => __( 'template.x_deleted', [ 'title' => 'Sport' ] ) ] );
    }

    // ─── Filter ───────────────────────────────────────────────────────────────

    private static function filter( $request, $model )
    {
        $filter = false;

        if ( !empty( $request->name ) ) {
            $model->where( 'name', 'LIKE', '%' . $request->name . '%' );
            $filter = true;
        }

        if ( !empty( $request->city ) ) {
            $model->where( 'city', 'LIKE', '%' . $request->city . '%' );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        return [ 'filter' => $filter, 'model' => $model ];
    }
}
