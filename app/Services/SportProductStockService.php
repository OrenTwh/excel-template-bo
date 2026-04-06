<?php

namespace App\Services;

use Illuminate\Support\Facades\{DB, Validator};
use Helper;
use App\Models\{SportProductVariant, SportProductStock, SportProductStockLog};

class SportProductStockService
{
    public static function adjustStock($request)
    {
        $id      = Helper::decode($request->variant_id);
        $variant = SportProductVariant::with('stock')->find($id);

        if (!$variant) {
            return response()->json(['message' => 'Variant not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'quantity_change' => ['required', 'integer', 'not_in:0'],
            'type'            => ['required', 'in:restock,adjustment,return'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ]);

        $validator->validate();

        DB::beginTransaction();
        try {
            $stock = $variant->stock ?? SportProductStock::create([
                'variant_id'        => $variant->id,
                'quantity'          => 0,
                'reserved_quantity' => 0,
            ]);

            $newQty = max(0, $stock->quantity + (int) $request->quantity_change);
            $stock->quantity = $newQty;
            $stock->save();

            SportProductStockLog::create([
                'variant_id'          => $variant->id,
                'quantity_change'     => (int) $request->quantity_change,
                'quantity_after'      => $newQty,
                'type'                => $request->type,
                'notes'               => $request->notes,
                'created_by_admin_id' => auth('admin')->id(),
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage() . ' line: ' . $th->getLine()], 500);
        }

        return response()->json([
            'message'       => __('template.record_updated_successfully'),
            'quantity'      => $newQty,
            'available'     => max(0, $newQty - $stock->reserved_quantity),
        ]);
    }

    public static function getStockLogs($request)
    {
        $id      = Helper::decode($request->variant_id);
        $variant = SportProductVariant::find($id);

        if (!$variant) {
            return response()->json(['message' => 'Variant not found.'], 404);
        }

        $logs = SportProductStockLog::where('variant_id', $variant->id)
            ->latest()
            ->paginate($request->input('per_page', 20));

        return response()->json($logs);
    }

    public static function getVariantStock($request)
    {
        $id      = Helper::decode($request->variant_id);
        $variant = SportProductVariant::with('stock')->find($id);

        if (!$variant) {
            return response()->json(['message' => 'Variant not found.'], 404);
        }

        $stock = $variant->stock;

        return response()->json([
            'variant_id'        => $variant->encrypted_id,
            'variant_name'      => $variant->name,
            'quantity'          => $stock?->quantity ?? 0,
            'reserved_quantity' => $stock?->reserved_quantity ?? 0,
            'available'         => $variant->available_stock,
        ]);
    }
}
