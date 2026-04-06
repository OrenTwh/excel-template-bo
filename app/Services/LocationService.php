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
    Location,
};

use Carbon\Carbon;

class LocationService
{
    public static function createLocation($request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:locations,slug'],
            'type' => ['required', 'in:state,city,area,district'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:locations,id'],
            'code' => ['nullable', 'string', 'max:50'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $attributeName = [
            'name' => __('Name'),
            'slug' => __('Slug'),
            'type' => __('Type'),
            'description' => __('Description'),
            'parent_id' => __('Parent Location'),
            'code' => __('Code'),
            'latitude' => __('Latitude'),
            'longitude' => __('Longitude'),
        ];

        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }

        $validator->setAttributeNames($attributeName)->validate();

        DB::beginTransaction();

        try {
            $locationData = [
                'parent_id' => $request->parent_id,
                'name' => $request->name,
                'slug' => $request->slug,
                'type' => $request->type,
                'description' => $request->description,
                'code' => $request->code,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'active' => $request->has('active') ? true : false,
                'status' => 10,
            ];

            $location = Location::create($locationData);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }

        return response()->json([
            'message' => __('template.new_x_created', ['title' => 'Location']),
            'data' => [
                'id' => $location->id,
                'encrypted_id' => $location->encrypted_id,
            ],
            'status' => 200
        ]);
    }

    public static function updateLocation($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        $validator = Validator::make($request->all(), [
            'id' => ['required', 'exists:locations,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:locations,slug,' . $request->id],
            'type' => ['required', 'in:state,city,area,district'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:locations,id'],
            'code' => ['nullable', 'string', 'max:50'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $attributeName = [
            'name' => __('Name'),
            'slug' => __('Slug'),
            'type' => __('Type'),
            'description' => __('Description'),
            'parent_id' => __('Parent Location'),
            'code' => __('Code'),
            'latitude' => __('Latitude'),
            'longitude' => __('Longitude'),
        ];

        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }

        $validator->setAttributeNames($attributeName)->validate();

        // Prevent setting self as parent
        if ($request->parent_id == $request->id) {
            return response()->json([
                'message' => __('A location cannot be its own parent'),
                'errors' => [
                    'parent_id' => __('A location cannot be its own parent')
                ]
            ], 422);
        }

        DB::beginTransaction();

        try {
            $location = Location::find($request->id);

            // Check if new parent would create a circular reference
            if ($request->parent_id) {
                $parent = Location::find($request->parent_id);
                $ancestors = $parent->ancestors();

                if ($ancestors->contains('id', $request->id)) {
                    DB::rollback();
                    return response()->json([
                        'message' => __('This would create a circular reference'),
                        'errors' => [
                            'parent_id' => __('This would create a circular reference')
                        ]
                    ], 422);
                }
            }

            $location->parent_id = $request->parent_id;
            $location->name = $request->name;
            $location->slug = $request->slug;
            $location->type = $request->type;
            $location->description = $request->description;
            $location->code = $request->code;
            $location->latitude = $request->latitude;
            $location->longitude = $request->longitude;
            $location->active = $request->has('active') ? true : false;

            $location->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }

        return response()->json([
            'message' => __('template.x_updated', ['title' => 'Location']),
        ]);
    }

    public static function allLocations($request)
    {
        $locations = Location::select('locations.*')
            ->with('parent:id,name');

        $filterObject = self::filter($request, $locations);
        $location = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ($request->input('order.0.column') != 0) {
            $dir = $request->input('order.0.dir');
            switch ($request->input('order.0.column')) {
                case 1:
                    $location->orderBy('locations.name', $dir);
                    break;
                case 2:
                    $location->orderBy('locations.type', $dir);
                    break;
                case 3:
                    $location->orderBy('locations.created_at', $dir);
                    break;
            }
        }

        $locationCount = $location->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $locations = $location->skip($offset)->take($limit)->get();

        if ($locations) {
            $locations->append([
                'encrypted_id',
                'full_path',
            ]);
        }

        $totalRecord = Location::count();

        $data = [
            'locations' => $locations,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $locationCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json($data);
    }

    private static function filter($request, $model)
    {
        $filter = false;

        if (!empty($request->name)) {
            $model->where('locations.name', 'LIKE', '%' . $request->name . '%');
            $filter = true;
        }

        if (!empty($request->slug)) {
            $model->where('locations.slug', 'LIKE', '%' . $request->slug . '%');
            $filter = true;
        }

        if (!empty($request->type)) {
            $model->where('locations.type', $request->type);
            $filter = true;
        }

        if (!empty($request->parent_id)) {
            $model->where('locations.parent_id', $request->parent_id);
            $filter = true;
        }

        if (!empty($request->status)) {
            $model->where('status', $request->status);
            $filter = true;
        }

        if (!empty($request->custom_search)) {
            $model->where(function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->custom_search . '%')
                    ->orWhere('slug', 'LIKE', '%' . $request->custom_search . '%')
                    ->orWhere('code', 'LIKE', '%' . $request->custom_search . '%');
            });
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneLocation($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        $location = Location::with('parent')->find($request->id);

        $location->append(['encrypted_id', 'full_path']);

        return response()->json($location);
    }

    public static function deleteLocation($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        $validator = Validator::make($request->all(), [
            'id' => ['required'],
        ]);

        $attributeName = [
            'id' => __('ID'),
        ];

        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }

        $validator->setAttributeNames($attributeName)->validate();

        DB::beginTransaction();

        try {
            $location = Location::find($request->id);

            // Check if location has children
            if ($location->children()->count() > 0) {
                DB::rollback();
                return response()->json([
                    'message' => __('Cannot delete location with child locations'),
                ], 422);
            }

            // Check if location has courts
            if ($location->courts()->count() > 0) {
                DB::rollback();
                return response()->json([
                    'message' => __('Cannot delete location with associated courts'),
                ], 422);
            }

            $location->delete();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }

        return response()->json([
            'message' => __('template.x_deleted', ['title' => 'Location']),
        ]);
    }

    public static function updateLocationStatus($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        DB::beginTransaction();

        try {
            $location = Location::find($request->id);
            $location->status = $location->status == 10 ? 20 : 10;

            $location->save();
            DB::commit();

            return response()->json([
                'data' => [
                    'location' => $location,
                    'message_key' => 'update_location_success',
                ]
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'update_location_failed',
            ], 500);
        }
    }

    public static function getLocationsByParent($request)
    {
        $parentId = $request->parent_id ?? null;

        $locations = Location::where('parent_id', $parentId)
            ->where('status', 10)
            
            ->orderBy('name')
            ->get();

        return response()->json([
            'locations' => $locations,
        ]);
    }

    public static function getLocationHierarchy($request)
    {
        // Get all root locations (states)
        $roots = Location::whereNull('parent_id')
            ->where('status', 10)
            
            ->with('children')
            ->orderBy('name')
            ->get();

        return response()->json([
            'locations' => $roots,
        ]);
    }
}
