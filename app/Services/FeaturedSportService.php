<?php

namespace App\Services;

use Illuminate\Support\Facades\{ DB, Validator };
use Helper;
use App\Models\{ FeaturedSport, Sport };

class FeaturedSportService
{
    public static function getFeaturedSports($request)
    {
        $perPage = $request->input('per_page', 20);

        $featuredSports = FeaturedSport::where('status', 10)
            ->with(['sport:id,name,slug,icon'])
            ->orderBy('sequence')
            ->orderBy('id')
            ->paginate($perPage);

        $featuredSports->getCollection()->each(function ($item) {
            $item->append('encrypted_id');
            $item->sport?->append(['encrypted_id', 'icon_path']);
        });

        return $featuredSports;
    }

    public static function allFeaturedSports($request)
    {
        $items = FeaturedSport::with('sport:id,name')
            ->orderBy('sequence')
            ->orderBy('id');

        if (!empty($request->status)) {
            $items->where('status', $request->status);
        }

        $total  = FeaturedSport::count();
        $count  = $items->count();
        $limit  = $request->length == -1 ? 1000000 : $request->length;
        $result = $items->skip($request->start)->take($limit)->get();

        $result->append('encrypted_id');

        return response()->json([
            'featured_sports'  => $result,
            'draw'             => $request->draw,
            'recordsFiltered'  => $count,
            'recordsTotal'     => $total,
        ]);
    }

    public static function createFeaturedSport($request)
    {
        $validator = Validator::make($request->all(), [
            'sport_id' => ['required', 'exists:sports,id', 'unique:featured_sports,sport_id'],
        ]);

        $validator->setAttributeNames([
            'sport_id' => strtolower(__('Sport')),
            'sequence' => strtolower(__('Sequence')),
        ])->validate();

        DB::beginTransaction();

        try {
            FeaturedSport::create([
                'sport_id' => $request->sport_id,
                'sequence' => $request->sequence ?? 0,
                'status'   => 10,
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage() . ' line: ' . $th->getLine()], 500);
        }

        return response()->json(['message' => __('template.new_x_created', ['title' => 'Featured Sport'])]);
    }

    public static function updateFeaturedSportStatus($request)
    {
        $request->merge(['id' => Helper::decode($request->id)]);

        DB::beginTransaction();

        try {
            $item = FeaturedSport::findOrFail($request->id);
            $item->status = $item->status == 10 ? 20 : 10;
            $item->save();

            DB::commit();

            $label = $item->status == 10 ? __('datatables.activated') : __('datatables.suspended');
            return response()->json(['message' => __('template.x_updated', ['title' => 'Featured Sport']) . ' - ' . $label]);
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
            $item = FeaturedSport::findOrFail($request->id);
            $item->sequence = $request->sequence;
            $item->save();

            DB::commit();

            return response()->json(['message' => __('template.x_updated', ['title' => 'Featured Sport'])]);
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
                FeaturedSport::where('id', Helper::decode($encryptedId))
                    ->update(['sequence' => $seq + 1]);
            }
            DB::commit();
            return response()->json(['message' => __('template.x_updated', ['title' => 'Featured Sport order'])]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public static function deleteFeaturedSport($request)
    {
        $request->merge(['id' => Helper::decode($request->id)]);

        DB::beginTransaction();

        try {
            FeaturedSport::findOrFail($request->id)->delete();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }

        return response()->json(['message' => __('template.x_deleted', ['title' => 'Featured Sport'])]);
    }
}
