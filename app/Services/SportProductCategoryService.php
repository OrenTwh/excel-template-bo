<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{DB, Validator, Storage};
use Helper;
use App\Models\SportProductCategory;

class SportProductCategoryService
{
    public static function allCategories($request)
    {
        $categories = SportProductCategory::with('parent:id,name')
            ->select('sport_product_categories.*')
            ->orderBy('created_at', 'DESC');

        $filterObject = self::filter($request, $categories);
        $model  = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ($request->input('order.0.column') != 0) {
            $dir = $request->input('order.0.dir');
            switch ($request->input('order.0.column')) {
                case 1: $model->orderBy('name', $dir); break;
                case 2: $model->orderBy('sequence', $dir); break;
                case 3: $model->orderBy('status', $dir); break;
            }
        } else {
            $model->orderBy('sequence')->orderBy('id');
        }

        $total  = SportProductCategory::count();
        $count  = $model->count();
        $limit  = $request->length == -1 ? 1000000 : $request->length;
        $items  = $model->skip($request->start)->take($limit)->get();

        $items->append(['encrypted_id']);

        return response()->json([
            'categories'      => $items,
            'draw'            => $request->draw,
            'recordsFiltered' => $filter ? $count : $total,
            'recordsTotal'    => $total,
        ]);
    }

    public static function oneCategory($request)
    {
        $id       = Helper::decode($request->id);
        $category = SportProductCategory::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        $category->append(['encrypted_id']);
        return response()->json($category);
    }

    public static function createCategory($request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:sport_product_categories,id'],
            'sequence'  => ['nullable', 'integer'],
            'status'    => ['nullable', 'in:10,20'],
            'image'     => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        $validator->validate();

        DB::beginTransaction();
        try {
            $slug = Str::slug($request->name);
            $orig = $slug;
            $i    = 1;
            while (SportProductCategory::where('slug', $slug)->exists()) {
                $slug = $orig . '-' . $i++;
            }

            $data = [
                'parent_id' => $request->filled('parent_id') ? $request->parent_id : null,
                'name'      => $request->name,
                'slug'      => $slug,
                'sequence'  => $request->input('sequence', 0),
                'status'    => $request->input('status', 10),
            ];

            $category = SportProductCategory::create($data);

            if ($request->hasFile('image')) {
                $category->image = $request->file('image')->store('sport-product-categories', ['disk' => 'public']);
                $category->save();
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage() . ' line: ' . $th->getLine()], 500);
        }

        return response()->json(['message' => __('template.record_created_successfully'), 'id' => $category->encrypted_id]);
    }

    public static function updateCategory($request)
    {
        $id       = Helper::decode($request->id);
        $category = SportProductCategory::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'      => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:sport_product_categories,id'],
            'sequence'  => ['nullable', 'integer'],
            'status'    => ['nullable', 'in:10,20'],
            'image'     => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        $validator->validate();

        DB::beginTransaction();
        try {
            $category->parent_id = $request->filled('parent_id') ? $request->parent_id : null;
            $category->name      = $request->name;
            $category->sequence  = $request->input('sequence', $category->sequence);
            $category->status    = $request->input('status', $category->status);

            if ($request->hasFile('image')) {
                if ($category->image) Storage::disk('public')->delete($category->image);
                $category->image = $request->file('image')->store('sport-product-categories', ['disk' => 'public']);
            }

            $category->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage() . ' line: ' . $th->getLine()], 500);
        }

        return response()->json(['message' => __('template.record_updated_successfully')]);
    }

    public static function updateCategoryStatus($request)
    {
        $id       = Helper::decode($request->id);
        $category = SportProductCategory::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        $category->status = $request->status;
        $category->save();

        return response()->json(['message' => __('template.record_updated_successfully')]);
    }

    public static function deleteCategory($request)
    {
        $id       = Helper::decode($request->id);
        $category = SportProductCategory::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        DB::beginTransaction();
        try {
            if ($category->image) Storage::disk('public')->delete($category->image);
            $category->delete();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }

        return response()->json(['message' => __('template.record_deleted_successfully')]);
    }

    private static function filter($request, $model)
    {
        $filter = false;

        if (!empty($request->status)) {
            $model->where('status', $request->status);
            $filter = true;
        }

        if (!empty($request->parent_id)) {
            $model->where('parent_id', $request->parent_id);
            $filter = true;
        }

        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $model->where('name', 'like', "%{$search}%");
            $filter = true;
        }

        return ['filter' => $filter, 'model' => $model];
    }
}
