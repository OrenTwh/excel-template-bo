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
    Sport,
    SportsTag,
    FileManager,
};

use Carbon\Carbon;

class SportService
{
    public static function getSports($request)
    {
        $query = Sport::where('status', 10)->with('tags:id,name,slug');

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        $perPage = $request->input('per_page', 20);
        $sports  = $query->orderBy('sequence')->orderBy('name')->paginate($perPage);

        $sports->getCollection()->append(['encrypted_id', 'icon_path']);

        return $sports;
    }

    public static function getOneSport($request)
    {
        $id    = Helper::decode($request->id);
        $sport = Sport::where('status', 10)
            
            ->with(['tags:id,name,slug'])
            ->find($id);

        if ($sport) {
            $sport->append(['encrypted_id', 'icon_path']);
        }

        return $sport;
    }

    public static function createSport($request)
    {
        $typeValues          = implode(',', array_keys(Sport::typeOptions()));
        $pricingMethodValues = implode(',', array_keys(Sport::pricingMethodOptions()));

        $validator = Validator::make($request->all(), [
            'name'           => ['required', 'string', 'max:255'],
            'slug'           => ['required', 'string', 'max:255', 'unique:sports,slug'],
            'type'           => ['nullable', 'string', 'in:' . $typeValues],
            'pricing_method' => ['nullable', 'string', 'in:' . $pricingMethodValues],
            'description'    => ['nullable', 'string'],
            'min_players'    => ['required', 'integer', 'min:1'],
            'max_players'    => ['nullable', 'integer', 'min:1'],
            'icon'           => ['nullable'],
            'image'          => ['nullable'],
            'thumbnail'      => ['nullable'],
        ]);

        $attributeName = [
            'name'           => __('Name'),
            'slug'           => __('Slug'),
            'type'           => __('Type'),
            'pricing_method' => __('Pricing Method'),
            'description'    => __('Description'),
            'min_players'    => __('Minimum Players'),
            'max_players'    => __('Maximum Players'),
            'icon'           => __('Icon'),
            'image'          => __('Image'),
            'thumbnail'      => __('Thumbnail'),
        ];

        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }

        $validator->setAttributeNames($attributeName)->validate();

        DB::beginTransaction();

        try {
            $sportCreate = Sport::create([
                'name'           => $request->name,
                'slug'           => $request->slug,
                'type'           => $request->type ?: null,
                'pricing_method' => $request->pricing_method ?: null,
                'description'    => $request->description,
                'min_players'    => $request->min_players,
                'max_players'    => $request->max_players,
                'active'         => $request->has('active') ? true : false,
                'status'         => 10,
            ]);

            $icon = explode(',', $request->icon);

            $imageFiles = FileManager::whereIn('id', $icon)->get();

            if ($imageFiles) {
                foreach ($imageFiles as $imageFile) {

                    $fileName = explode('/', $imageFile->file);

                    $target = 'sports/' . $sportCreate->id . '/' . $fileName[1];
                    Storage::disk('public')->move($imageFile->file, $target);

                    $sportCreate->icon = $target;
                    $sportCreate->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $image = explode(',', $request->image);
            $imageFiles = FileManager::whereIn('id', $image)->get();
            if ($imageFiles) {
                foreach ($imageFiles as $imageFile) {
                    $fileName = explode('/', $imageFile->file);
                    $target = 'sports/' . $sportCreate->id . '/' . $fileName[1];
                    Storage::disk('public')->move($imageFile->file, $target);
                    $sportCreate->image = $target;
                    $sportCreate->save();
                    $imageFile->status = 10;
                    $imageFile->save();
                }
            }

            $thumbnail = explode(',', $request->thumbnail);
            $thumbFiles = FileManager::whereIn('id', $thumbnail)->get();
            if ($thumbFiles) {
                foreach ($thumbFiles as $thumbFile) {
                    $fileName = explode('/', $thumbFile->file);
                    $target = 'sports/' . $sportCreate->id . '/' . $fileName[1];
                    Storage::disk('public')->move($thumbFile->file, $target);
                    $sportCreate->thumbnail = $target;
                    $sportCreate->save();
                    $thumbFile->status = 10;
                    $thumbFile->save();
                }
            }

            $sportCreate->tags()->sync( self::resolveTagIds( $request->tags ) );

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }

        return response()->json([
            'message' => __('template.new_x_created', ['title' => 'Sport']),
            'data' => [
                'id' => $sportCreate->id,
                'encrypted_id' => $sportCreate->encrypted_id,
            ],
            'status' => 200
        ]);
    }

    public static function updateSport($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        $typeValues          = implode(',', array_keys(Sport::typeOptions()));
        $pricingMethodValues = implode(',', array_keys(Sport::pricingMethodOptions()));

        $validator = Validator::make($request->all(), [
            'id'             => ['required', 'exists:sports,id'],
            'name'           => ['required', 'string', 'max:255'],
            'slug'           => ['required', 'string', 'max:255', 'unique:sports,slug,' . $request->id],
            'type'           => ['nullable', 'string', 'in:' . $typeValues],
            'pricing_method' => ['nullable', 'string', 'in:' . $pricingMethodValues],
            'description'    => ['nullable', 'string'],
            'min_players'    => ['required', 'integer', 'min:1'],
            'max_players'    => ['nullable', 'integer', 'min:1'],
            'icon'           => ['nullable'],
            'image'          => ['nullable'],
            'thumbnail'      => ['nullable'],
        ]);

        $attributeName = [
            'name'           => __('Name'),
            'slug'           => __('Slug'),
            'type'           => __('Type'),
            'pricing_method' => __('Pricing Method'),
            'description'    => __('Description'),
            'min_players'    => __('Minimum Players'),
            'max_players'    => __('Maximum Players'),
            'icon'           => __('Icon'),
            'image'          => __('Image'),
            'thumbnail'      => __('Thumbnail'),
        ];

        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }

        $validator->setAttributeNames($attributeName)->validate();

        DB::beginTransaction();

        try {
            $updateSport = Sport::find($request->id);

            $updateSport->name           = $request->name;
            $updateSport->slug           = $request->slug;
            $updateSport->type           = $request->type ?: null;
            $updateSport->pricing_method = $request->pricing_method ?: null;
            $updateSport->description    = $request->description;
            $updateSport->min_players    = $request->min_players;
            $updateSport->max_players    = $request->max_players;
            $updateSport->active         = $request->has('active') ? true : false;

            $icon = explode(',', $request->icon);

            $imageFiles = FileManager::whereIn('id', $icon)->get();

            if ($imageFiles) {
                foreach ($imageFiles as $imageFile) {

                    $fileName = explode('/', $imageFile->file);

                    $target = 'sports/' . $updateSport->id . '/' . $fileName[1];
                    Storage::disk('public')->move($imageFile->file, $target);

                    $updateSport->icon = $target;
                    $updateSport->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $image = explode(',', $request->image);
            $imageFiles = FileManager::whereIn('id', $image)->get();
            if ($imageFiles) {
                foreach ($imageFiles as $imageFile) {
                    $fileName = explode('/', $imageFile->file);
                    $target = 'sports/' . $updateSport->id . '/' . $fileName[1];
                    Storage::disk('public')->move($imageFile->file, $target);
                    $updateSport->image = $target;
                    $updateSport->save();
                    $imageFile->status = 10;
                    $imageFile->save();
                }
            }

            $thumbnail = explode(',', $request->thumbnail);
            $thumbFiles = FileManager::whereIn('id', $thumbnail)->get();
            if ($thumbFiles) {
                foreach ($thumbFiles as $thumbFile) {
                    $fileName = explode('/', $thumbFile->file);
                    $target = 'sports/' . $updateSport->id . '/' . $fileName[1];
                    Storage::disk('public')->move($thumbFile->file, $target);
                    $updateSport->thumbnail = $target;
                    $updateSport->save();
                    $thumbFile->status = 10;
                    $thumbFile->save();
                }
            }

            $updateSport->save();

            $updateSport->tags()->sync( self::resolveTagIds( $request->tags ) );

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }

        return response()->json([
            'message' => __('template.x_updated', ['title' => 'Sport']),
        ]);
    }

    public static function allSports($request)
    {
        $sports = Sport::select('sports.*');

        $filterObject = self::filter($request, $sports);
        $sport = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ($request->input('order.0.column') != 0) {
            $dir = $request->input('order.0.dir');
            switch ($request->input('order.0.column')) {
                case 4:
                    $sport->orderBy('sports.name', $dir);
                    break;
                case 5:
                    $sport->orderBy('sports.slug', $dir);
                    break;
                default:
                    $sport->orderBy('sports.sequence', 'asc');
                    break;
            }
        } else {
            $sport->orderBy('sports.sequence', 'asc');
        }

        $sportCount = $sport->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $sports = $sport->skip($offset)->take($limit)->get();

        if ($sports) {
            $sports->append([
                'encrypted_id',
                'icon_path',
            ]);
        }

        $totalRecord = Sport::count();

        $data = [
            'sports' => $sports,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $sportCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json($data);
    }

    public static function updateSequence($request)
    {
        $request->merge(['id' => Helper::decode($request->id)]);

        DB::beginTransaction();

        try {
            $sport           = Sport::findOrFail($request->id);
            $sport->sequence = (int) $request->sequence;
            $sport->save();

            DB::commit();

            return response()->json([
                'message' => __('template.x_updated', ['title' => 'Sport']),
            ]);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public static function reorder($request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['message' => 'No IDs provided'], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($ids as $seq => $encryptedId) {
                Sport::where('id', Helper::decode($encryptedId))
                    ->update(['sequence' => $seq + 1]);
            }
            DB::commit();
            return response()->json(['message' => __('template.x_updated', ['title' => 'Sport order'])]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    private static function filter($request, $model)
    {
        $filter = false;

        if (!empty($request->name)) {
            $model->where('sports.name', 'LIKE', '%' . $request->name . '%');
            $filter = true;
        }

        if (!empty($request->slug)) {
            $model->where('sports.slug', 'LIKE', '%' . $request->slug . '%');
            $filter = true;
        }

        if (!empty($request->status)) {
            $model->where('status', $request->status);
            $filter = true;
        }

        if (!empty($request->custom_search)) {
            $model->where(function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->custom_search . '%')
                    ->orWhere('slug', 'LIKE', '%' . $request->custom_search . '%');
            });
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneSport($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        $sport = Sport::with('tags')->find($request->id);

        $sport->append(['encrypted_id', 'icon_path', 'image_path', 'thumbnail_path']);

        return response()->json($sport);
    }

    public static function deleteSport($request)
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
            $sport = Sport::find($request->id);

            if ($sport->icon) {
                Storage::disk('public')->delete($sport->icon);
            }

            if ($sport->image) {
                Storage::disk('public')->delete($sport->image);
            }

            if ($sport->thumbnail) {
                Storage::disk('public')->delete($sport->thumbnail);
            }

            $sport->delete();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }

        return response()->json([
            'message' => __('template.x_deleted', ['title' => 'Sport']),
        ]);
    }

    public static function updateSportStatus($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        DB::beginTransaction();

        try {
            $sport = Sport::find($request->id);
            $sport->status = $sport->status == 10 ? 20 : 10;

            $sport->save();
            DB::commit();

            return response()->json([
                'data' => [
                    'sport' => $sport,
                    'message_key' => 'update_sport_success',
                ]
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'update_sport_failed',
            ], 500);
        }
    }

    public static function removeIconImage($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        $sport = Sport::find($request->id);

        if ($sport->icon) {
            Storage::disk('public')->delete($sport->icon);
            $sport->icon = null;
            $sport->save();
        }

        return response()->json([
            'message' => __('template.x_updated', ['title' => 'Icon']),
        ]);
    }

    public static function removeImage($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        $sport = Sport::find($request->id);

        if ($sport->image) {
            Storage::disk('public')->delete($sport->image);
            $sport->image = null;
            $sport->save();
        }

        return response()->json([
            'message' => __('template.x_updated', ['title' => 'Image']),
        ]);
    }

    public static function removeThumbnail($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        $sport = Sport::find($request->id);

        if ($sport->thumbnail) {
            Storage::disk('public')->delete($sport->thumbnail);
            $sport->thumbnail = null;
            $sport->save();
        }

        return response()->json([
            'message' => __('template.x_updated', ['title' => 'Thumbnail']),
        ]);
    }

    private static function resolveTagIds( $tagsJson ): array
    {
        $tags = json_decode( $tagsJson, true );

        if ( empty( $tags ) ) {
            return [];
        }

        $ids = [];

        foreach ( $tags as $tag ) {
            if ( is_numeric( $tag ) ) {
                $ids[] = (int) $tag;
            } elseif ( str_starts_with( $tag, 'new:' ) ) {
                $name    = trim( substr( $tag, 4 ) );
                $slug    = Str::slug( $name );
                $newTag  = SportsTag::firstOrCreate(
                    [ 'slug' => $slug ],
                    [ 'name' => $name, 'status' => 10 ]
                );
                $ids[] = $newTag->id;
            }
        }

        return $ids;
    }
}
