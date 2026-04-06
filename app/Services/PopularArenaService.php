<?php

namespace App\Services;

use Illuminate\Support\Facades\{ DB, Validator };
use Helper;
use App\Models\{ PopularArena, Venue };

class PopularArenaService
{
    public static function getPopularArenas($request)
    {
        $perPage = $request->input('per_page', 20);

        $popularArenas = PopularArena::where('status', 10)
            ->with([
                'venue:id,name,slug,address_1,city,state,latitude,longitude,image',
                'venue.sports' => fn($q) => $q->where('venue_sports.status', 10)
                    ->select('sports.id', 'sports.name', 'sports.slug', 'sports.icon'),
            ])
            ->orderBy('sequence')
            ->orderBy('id')
            ->paginate($perPage);

        $popularArenas->getCollection()->each(function ($item) {
            $item->append('encrypted_id');
            if ($item->venue) {
                $item->venue->append(['encrypted_id', 'image_path']);
                $item->venue->sports->each(fn($s) => $s->append(['encrypted_id', 'icon_path']));
            }
        });

        return $popularArenas;
    }

    public static function allPopularArenas($request)
    {
        $items = PopularArena::with('venue:id,name')
            ->orderBy('sequence')
            ->orderBy('id');

        if (!empty($request->status)) {
            $items->where('status', $request->status);
        }

        $total  = PopularArena::count();
        $count  = $items->count();
        $limit  = $request->length == -1 ? 1000000 : $request->length;
        $result = $items->skip($request->start)->take($limit)->get();

        $result->append('encrypted_id');

        return response()->json([
            'popular_arenas'  => $result,
            'draw'            => $request->draw,
            'recordsFiltered' => $count,
            'recordsTotal'    => $total,
        ]);
    }

    public static function createPopularArena($request)
    {
        $validator = Validator::make($request->all(), [
            'venue_id' => ['required', 'exists:venues,id', 'unique:popular_arenas,venue_id'],
        ]);

        $validator->setAttributeNames([
            'venue_id' => strtolower(__('Venue')),
            'sequence' => strtolower(__('Sequence')),
        ])->validate();

        DB::beginTransaction();

        try {
            PopularArena::create([
                'venue_id' => $request->venue_id,
                'sequence' => $request->sequence ?? 0,
                'status'   => 10,
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage() . ' line: ' . $th->getLine()], 500);
        }

        return response()->json(['message' => __('template.new_x_created', ['title' => 'Popular Arena'])]);
    }

    public static function updatePopularArenaStatus($request)
    {
        $request->merge(['id' => Helper::decode($request->id)]);

        DB::beginTransaction();

        try {
            $item = PopularArena::findOrFail($request->id);
            $item->status = $item->status == 10 ? 20 : 10;
            $item->save();

            DB::commit();

            $label = $item->status == 10 ? __('datatables.activated') : __('datatables.suspended');
            return response()->json(['message' => __('template.x_updated', ['title' => 'Popular Arena']) . ' - ' . $label]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public static function updateSequence($request)
    {
        $request->merge(['id' => Helper::decode($request->id)]);

        DB::beginTransaction();

        try {
            $item = PopularArena::findOrFail($request->id);
            $item->sequence = $request->sequence;
            $item->save();

            DB::commit();

            return response()->json(['message' => __('template.x_updated', ['title' => 'Popular Arena'])]);
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
                PopularArena::where('id', Helper::decode($encryptedId))
                    ->update(['sequence' => $seq + 1]);
            }
            DB::commit();
            return response()->json(['message' => __('template.x_updated', ['title' => 'Popular Arena order'])]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public static function deletePopularArena($request)
    {
        $request->merge(['id' => Helper::decode($request->id)]);

        DB::beginTransaction();

        try {
            PopularArena::findOrFail($request->id)->delete();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }

        return response()->json(['message' => __('template.x_deleted', ['title' => 'Popular Arena'])]);
    }
}
