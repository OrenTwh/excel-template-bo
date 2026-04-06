<?php

namespace App\Services;

use App\Models\CmsArticle;
use App\Models\CmsArticleTranslation;
use App\Models\CmsArticleBanner;
use Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CMSService
{
    public function __construct() {}

    public static function allProjects($request)
    {
        $query = CmsArticle::with(['translations', 'banners'])->whereIn('status', [10,11])->orderBy('created_at', 'desc');

        // Search filters
        if (!empty($request['title'])) {
            $query->whereHas('translations', function ($q) use ($request) {
                $q->where('title', 'LIKE', '%' . $request['title'] . '%');
            });
        }

        if (!empty($request['status'])) {
            $query->where('status', $request['status']);
        }

        if ( !empty( $request->publish_date ) ) {
            if ( str_contains( $request->publish_date, 'to' ) ) {
                $dates = explode( ' to ', $request->publish_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $query->whereBetween( 'publish_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->publish_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $query->whereBetween( 'publish_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        // Pagination
        $start = $request['start'] ?? 0;
        $length = $request['length'] ?? 10;

        $totalRecords = $query->count();

        $articles = $query->offset($start)
                         ->limit($length)
                         ->orderBy('publish_date', 'desc')
                         ->get();

        $articles->append(['thumbnail_path']);

        $articles->map(function ($article) {
            $article->encrypted_id = Helper::encode($article->id);
            $article->publish_date = $article->publish_date ? Carbon::parse($article->publish_date)->format('Y-m-d') : '-';

            // Get English title or first available translation
            $translation = $article->translations->where('locale', 'en')->first()
                        ?? $article->translations->first();
            $article->title = $translation ? $translation->title : '-';

            return $article;
        });

        return [
            'articles' => $articles,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
        ];
    }

    public static function oneProject($request)
    {
        $decodedId = Helper::decode($request->id);

        $article = CmsArticle::with(['translations', 'banners' => function($query) {
            $query->orderBy('sort_order');
        }])->find($decodedId);

        if (!$article) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found'
            ], 404);
        }

        // Format the data for the frontend
        $formattedArticle = [
            'id' => $article->id,
            'encrypted_id' => Helper::encode($article->id),
            'thumbnail' => $article->thumbnail,
            'thumbnail_path' => $article->thumbnail_path,
            'publish_date' => $article->publish_date,
            'status' => $article->status,
            'translations' => [],
            'banners' => [],
        ];

        // Format translations by locale
        foreach ($article->translations as $translation) {
            $formattedArticle['translations'][$translation->locale] = [
                'title' => $translation->title,
                'slug' => $translation->slug,
                'short_description' => $translation->short_description,
                'description' => $translation->description,
                'meta_title' => $translation->meta_title,
                'meta_description' => $translation->meta_description,
                'meta_keywords' => $translation->meta_keywords,
            ];
        }

        // Format banners
        foreach ($article->banners as $banner) {
            $formattedArticle['banners'][] = [
                'id' => $banner->id,
                'encrypted_id' => Helper::encode($banner->id),
                'image' => $banner->image,
                'image_path' => $banner->image_path,
                'alt_text' => $banner->alt_text,
                'sort_order' => $banner->sort_order,
                'target_page' => $banner->target_page,
                'target_page_web' => $banner->target_page_web,
            ];
        }

        return response()->json($formattedArticle);
    }

    public static function createProject($request)
    {

        $rules = [
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:25120'],
            'publish_date' => ['required', 'date'],
            'status' => ['required', 'in:11,10'],

            // Translations for each locale
            'title_en' => ['nullable', 'string', 'max:255'],
            'title_zh_tw' => ['nullable', 'string', 'max:255'],
            'title_id' => ['nullable', 'string', 'max:255'],
            'title_zh_cn' => ['nullable', 'string', 'max:255'],
            'title_ja' => ['nullable', 'string', 'max:255'],
            'title_ko' => ['nullable', 'string', 'max:255'],

            'slug_en' => ['nullable', 'string', 'max:255'],
            'slug_zh_tw' => ['nullable', 'string', 'max:255'],
            'slug_id' => ['nullable', 'string', 'max:255'],
            'slug_zh_cn' => ['nullable', 'string', 'max:255'],
            'slug_ja' => ['nullable', 'string', 'max:255'],
            'slug_ko' => ['nullable', 'string', 'max:255'],

            'short_description_en' => ['nullable', 'string'],
            'short_description_zh_tw' => ['nullable', 'string'],
            'short_description_id' => ['nullable', 'string'],
            'short_description_zh_cn' => ['nullable', 'string'],
            'short_description_ja' => ['nullable', 'string'],
            'short_description_ko' => ['nullable', 'string'],

            'description_en' => ['nullable', 'string'],
            'description_zh_tw' => ['nullable', 'string'],
            'description_id' => ['nullable', 'string'],
            'description_zh_cn' => ['nullable', 'string'],
            'description_ja' => ['nullable', 'string'],
            'description_ko' => ['nullable', 'string'],

            'meta_title_en' => ['nullable', 'string', 'max:255'],
            'meta_title_zh_tw' => ['nullable', 'string', 'max:255'],
            'meta_title_id' => ['nullable', 'string', 'max:255'],
            'meta_title_zh_cn' => ['nullable', 'string', 'max:255'],
            'meta_title_ja' => ['nullable', 'string', 'max:255'],
            'meta_title_ko' => ['nullable', 'string', 'max:255'],

            'meta_description_en' => ['nullable', 'string'],
            'meta_description_zh_tw' => ['nullable', 'string'],
            'meta_description_id' => ['nullable', 'string'],
            'meta_description_zh_cn' => ['nullable', 'string'],
            'meta_description_ja' => ['nullable', 'string'],
            'meta_description_ko' => ['nullable', 'string'],

            'meta_keywords_en' => ['nullable', 'string'],
            'meta_keywords_zh_tw' => ['nullable', 'string'],
            'meta_keywords_id' => ['nullable', 'string'],
            'meta_keywords_zh_cn' => ['nullable', 'string'],
            'meta_keywords_ja' => ['nullable', 'string'],
            'meta_keywords_ko' => ['nullable', 'string'],

            // Banners
            'banner_images' => ['nullable', 'array'],
            'banner_images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:25120'],
            'banner_alt_texts' => ['nullable', 'array'],
            'banner_target_pages' => ['nullable', 'array'],
            'banner_target_pages_web' => ['nullable', 'array'],
        ];

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) {
            foreach ($validator->errors()->getMessages() as $key => $messages) {
                if (preg_match('/banner_images\.(\d+)/', $key, $matches)) {
                    $index = $matches[1] + 1;
                    foreach ($messages as &$msg) {
                        $msg = "Banner #{$index}: {$msg}";
                    }
                    $validator->errors()->add($key, implode(' ', $messages));
                }
            }
        });

        $validator->validate();

        DB::beginTransaction();

        try {
            // Create article
            $article = new CmsArticle();
            $article->publish_date = $request->publish_date;
            $article->status = $request->status;

            // Handle thumbnail upload
            if ($request->hasFile('thumbnail')) {
                $file = $request->file('thumbnail');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('admin/images/cms_articles', $filename, 'public');
                $article->thumbnail = $path;
            }

            $article->save();

            // Create translations for all locales
            $locales = ['en', 'zh_tw', 'id', 'zh_cn', 'ja', 'ko'];
            foreach ($locales as $locale) {
                // Only create translation if at least title is provided
                if (!empty($request->input("title_{$locale}"))) {
                    CmsArticleTranslation::create([
                        'cms_article_id' => $article->id,
                        'locale' => $locale,
                        'title' => $request->input("title_{$locale}") ?? $request->input('title_en'),
                        'description' => $request->input("description_{$locale}") ?? $request->input('description_en'),
                        'short_description' => $request->input("short_description_{$locale}") ?? $request->input('short_description_en'),
                        'meta_title' => $request->input("meta_title_{$locale}") ?? $request->input('meta_title_en'),
                        'meta_description' => $request->input("meta_description_{$locale}") ?? $request->input('meta_description_en'),
                        'meta_keywords' => $request->input("meta_keywords_{$locale}") ?? $request->input('meta_keywords_en'),
                        'slug' => $request->input("slug_{$locale}") ?? $request->input('slug_en'),
                    ]);
                }
            }

            // Handle banners
            if ($request->hasFile('banner_images')) {
                $bannerImages = $request->file('banner_images');
                $bannerAltTexts = $request->input('banner_alt_texts', []);
                $bannerTargetPages = $request->input('banner_target_pages', []);
                $bannerTargetPagesWeb = $request->input('banner_target_pages_web', []);

                foreach ($bannerImages as $index => $image) {
                    if ($image) {
                        $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                        $path = $image->storeAs('admin/images/cms_article_banners', $filename, 'public');

                        CmsArticleBanner::create([
                            'cms_article_id' => $article->id,
                            'image' => $path,
                            'alt_text' => $bannerAltTexts[$index] ?? null,
                            'target_page' => $bannerTargetPages[$index] ?? null,
                            'target_page_web' => $bannerTargetPagesWeb[$index] ?? null,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            DB::commit();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($article)
                ->withProperties(['attributes' => $article->toArray()])
                ->log('created');

            return response()->json([
                'success' => true,
                'message' => 'CMS Article created successfully',
                'data' => $article
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create article: ' . $e->getMessage()
            ], 500);
        }
    }

    public static function updateProject($request)
    {

        $rules = [
            'id' => ['required'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:25120'],
            'publish_date' => ['required', 'date'],
            'status' => ['required', 'in:10,11'],

            // Translations for each locale
            'title_en' => ['nullable', 'string', 'max:255'],
            'title_zh_tw' => ['nullable', 'string', 'max:255'],
            'title_id' => ['nullable', 'string', 'max:255'],
            'title_zh_cn' => ['nullable', 'string', 'max:255'],
            'title_ja' => ['nullable', 'string', 'max:255'],
            'title_ko' => ['nullable', 'string', 'max:255'],

            'slug_en' => ['nullable', 'string', 'max:255'],
            'slug_zh_tw' => ['nullable', 'string', 'max:255'],
            'slug_id' => ['nullable', 'string', 'max:255'],
            'slug_zh_cn' => ['nullable', 'string', 'max:255'],
            'slug_ja' => ['nullable', 'string', 'max:255'],
            'slug_ko' => ['nullable', 'string', 'max:255'],

            'short_description_en' => ['nullable', 'string'],
            'short_description_zh_tw' => ['nullable', 'string'],
            'short_description_id' => ['nullable', 'string'],
            'short_description_zh_cn' => ['nullable', 'string'],
            'short_description_ja' => ['nullable', 'string'],
            'short_description_ko' => ['nullable', 'string'],

            'description_en' => ['nullable', 'string'],
            'description_zh_tw' => ['nullable', 'string'],
            'description_id' => ['nullable', 'string'],
            'description_zh_cn' => ['nullable', 'string'],
            'description_ja' => ['nullable', 'string'],
            'description_ko' => ['nullable', 'string'],

            'meta_title_en' => ['nullable', 'string', 'max:255'],
            'meta_title_zh_tw' => ['nullable', 'string', 'max:255'],
            'meta_title_id' => ['nullable', 'string', 'max:255'],
            'meta_title_zh_cn' => ['nullable', 'string', 'max:255'],
            'meta_title_ja' => ['nullable', 'string', 'max:255'],
            'meta_title_ko' => ['nullable', 'string', 'max:255'],

            'meta_description_en' => ['nullable', 'string'],
            'meta_description_zh_tw' => ['nullable', 'string'],
            'meta_description_id' => ['nullable', 'string'],
            'meta_description_zh_cn' => ['nullable', 'string'],
            'meta_description_ja' => ['nullable', 'string'],
            'meta_description_ko' => ['nullable', 'string'],

            'meta_keywords_en' => ['nullable', 'string'],
            'meta_keywords_zh_tw' => ['nullable', 'string'],
            'meta_keywords_id' => ['nullable', 'string'],
            'meta_keywords_zh_cn' => ['nullable', 'string'],
            'meta_keywords_ja' => ['nullable', 'string'],
            'meta_keywords_ko' => ['nullable', 'string'],

            // Banners
            'banner_images' => ['nullable', 'array'],
            'banner_images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:25120'],
            'banner_alt_texts' => ['nullable', 'array'],
            'banner_target_pages' => ['nullable', 'array'],
            'banner_target_pages_web' => ['nullable', 'array'],
            'existing_banner_ids' => ['nullable', 'array'],
        ];

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) {
            foreach ($validator->errors()->getMessages() as $key => $messages) {
                if (preg_match('/banner_images\.(\d+)/', $key, $matches)) {
                    $index = $matches[1] + 1;
                    foreach ($messages as &$msg) {
                        $msg = "Banner #{$index}: {$msg}";
                    }
                    $validator->errors()->add($key, implode(' ', $messages));
                }
            }
        });

        $validator->validate();

        $decodedId = Helper::decode($request->id);
        $article = CmsArticle::find($decodedId);

        if (!$article) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found'
            ], 404);
        }

        DB::beginTransaction();

        try {
            $oldAttributes = $article->toArray();

            $article->publish_date = $request->publish_date;
            $article->status = $request->status;

            // Handle thumbnail upload
            if ($request->hasFile('thumbnail')) {
                // Delete old thumbnail
                if ($article->thumbnail) {
                    Storage::disk('public')->delete($article->thumbnail);
                }

                $file = $request->file('thumbnail');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('admin/images/cms_articles', $filename, 'public');
                $article->thumbnail = $path;
            }

            $article->save();

            // Update translations for all locales
            $locales = ['en', 'zh_tw', 'id', 'zh_cn', 'ja', 'ko'];
            foreach ($locales as $locale) {
                // Find or create translation
                $translation = CmsArticleTranslation::where('cms_article_id', $article->id)
                    ->where('locale', $locale)
                    ->first();

                // Only update/create if at least title is provided
                if (!empty($request->input("title_{$locale}"))) {
                    if ($translation) {
                        $translation->update([
                            'title' => $request->input("title_{$locale}"),
                            'slug' => $request->input("slug_{$locale}"),
                            'short_description' => $request->input("short_description_{$locale}"),
                            'description' => $request->input("description_{$locale}"),
                            'meta_title' => $request->input("meta_title_{$locale}"),
                            'meta_description' => $request->input("meta_description_{$locale}"),
                            'meta_keywords' => $request->input("meta_keywords_{$locale}"),
                        ]);
                    } else {
                        CmsArticleTranslation::create([
                            'cms_article_id' => $article->id,
                            'locale' => $locale,
                            'title' => $request->input("title_{$locale}") ?? $request->input('title_en'),
                            'description' => $request->input("description_{$locale}") ?? $request->input('description_en'),
                            'short_description' => $request->input("short_description_{$locale}") ?? $request->input('short_description_en'),
                            'meta_title' => $request->input("meta_title_{$locale}") ?? $request->input('meta_title_en'),
                            'meta_description' => $request->input("meta_description_{$locale}") ?? $request->input('meta_description_en'),
                            'meta_keywords' => $request->input("meta_keywords_{$locale}") ?? $request->input('meta_keywords_en'),
                            'slug' => $request->input("slug_{$locale}") ?? $request->input('slug_en'),
                        ]);
                    }
                } else if ($translation) {
                    // Delete translation if title is empty
                    $translation->delete();
                }
            }

            // Handle banners - get existing banner IDs to keep
            $existingBannerIds = $request->input('existing_banner_ids', []);

            // Delete banners that are not in the existing list
            $bannersToDelete = CmsArticleBanner::where('cms_article_id', $article->id)
                ->whereNotIn('id', $existingBannerIds)
                ->get();

            foreach ($bannersToDelete as $banner) {
                if ($banner->image) {
                    Storage::disk('public')->delete($banner->image);
                }
                $banner->delete();
            }

            // Update sort order for existing banners
            foreach ($existingBannerIds as $index => $bannerId) {
                $banner = CmsArticleBanner::find($bannerId);
                if ($banner) {
                    $bannerAltTexts = $request->input('banner_alt_texts', []);
                    $bannerTargetPages = $request->input('banner_target_pages', []);
                    $bannerTargetPagesWeb = $request->input('banner_target_pages_web', []);

                    $banner->sort_order = $index;
                    $banner->alt_text = $bannerAltTexts[$index] ?? $banner->alt_text;
                    $banner->target_page = $bannerTargetPages[$index] ?? $banner->target_page;
                    $banner->target_page_web = $bannerTargetPagesWeb[$index] ?? $banner->target_page_web;
                    $banner->save();
                }
            }

            // Handle new banner uploads
            if ($request->hasFile('banner_images')) {
                $bannerImages = $request->file('banner_images');
                $bannerAltTexts = $request->input('banner_alt_texts', []);
                $bannerTargetPages = $request->input('banner_target_pages', []);
                $bannerTargetPagesWeb = $request->input('banner_target_pages_web', []);

                $startIndex = count($existingBannerIds);

                foreach ($bannerImages as $index => $image) {
                    if ($image) {
                        $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                        $path = $image->storeAs('admin/images/cms_article_banners', $filename, 'public');

                        $actualIndex = $index != 0 ? $startIndex + $index : $index;

                        CmsArticleBanner::create([
                            'cms_article_id' => $article->id,
                            'image' => $path,
                            'alt_text' => $bannerAltTexts[$index] ?? null,
                            'target_page' => $bannerTargetPages[$index] ?? null,
                            'target_page_web' => $bannerTargetPagesWeb[$index] ?? null,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            DB::commit();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($article)
                ->withProperties([
                    'old' => $oldAttributes,
                    'attributes' => $article->fresh()->toArray()
                ])
                ->log('updated');

            return response()->json([
                'success' => true,
                'message' => 'CMS Article updated successfully',
                'data' => $article
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update article: ' . $e->getMessage()
            ], 500);
        }
    }

    public static function updateProjectStatus($request)
    {
        $rules = [
            'id' => ['required'],
            'status' => ['required', 'in:10,11,20'],
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->validate();

        $decodedId = Helper::decode($request->id);
        $article = CmsArticle::find($decodedId);

        if (!$article) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found'
            ], 404);
        }

        $oldStatus = $article->status;
        $article->status = $request->status;
        $article->save();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($article)
            ->withProperties([
                'old_status' => $oldStatus,
                'new_status' => $request->status
            ])
            ->log('status_updated');

        $statusText = $request->status == 1 ? 'Published' : 'Draft';

        return response()->json([
            'success' => true,
            'message' => "Article status updated to {$statusText} successfully"
        ]);
    }

    public static function deleteProject($request)
    {
        $rules = [
            'id' => ['required'],
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->validate();

        $decodedId = Helper::decode($request->id);
        $article = CmsArticle::find($decodedId);

        if (!$article) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found'
            ], 404);
        }

        DB::beginTransaction();

        try {
            // Delete thumbnail
            if ($article->thumbnail) {
                Storage::disk('public')->delete($article->thumbnail);
            }

            // Delete all banner images
            foreach ($article->banners as $banner) {
                if ($banner->image) {
                    Storage::disk('public')->delete($banner->image);
                }
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($article)
                ->withProperties(['attributes' => $article->toArray()])
                ->log('deleted');

            // Soft delete the article (translations and banners will cascade)
            $article->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'CMS Article deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete article: ' . $e->getMessage()
            ], 500);
        }
    }

    public static function ckeUpload($request)
    {
        if ($request->hasFile('upload')) {
            $file = $request->file('upload');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('admin/images/cms_articles/ckeditor', $filename, 'public');

            $url = asset('storage/' . $path);

            return response()->json([
                'uploaded' => true,
                'url' => $url
            ]);
        }

        return response()->json([
            'uploaded' => false,
            'error' => [
                'message' => 'No file uploaded'
            ]
        ]);
    }

    public static function removeThumbnail($request)
    {
        $rules = [
            'id' => ['required'],
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->validate();

        $decodedId = Helper::decode($request->id);
        $article = CmsArticle::find($decodedId);

        if (!$article) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found'
            ], 404);
        }

        if ($article->thumbnail) {
            Storage::disk('public')->delete($article->thumbnail);
            $article->thumbnail = null;
            $article->save();

            return response()->json([
                'success' => true,
                'message' => 'Thumbnail removed successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No thumbnail to remove'
        ], 400);
    }

    public static function deleteBanner($request)
    {
        $rules = [
            'id' => ['required'],
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->validate();

        $decodedId = Helper::decode($request->id);
        $banner = CmsArticleBanner::find($decodedId);

        if (!$banner) {
            return response()->json([
                'success' => false,
                'message' => 'Banner not found'
            ], 404);
        }

        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->delete();

        return response()->json([
            'success' => true,
            'message' => 'Banner deleted successfully'
        ]);
    }

    /**
     * API Methods
     */

    public static function getArticles($request)
    {
        // Validate request parameters
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string|max:255',
            'locale' => 'nullable|string|in:en,zh_tw,id,zh_cn,ja,ko',
            'status' => 'nullable|integer|in:10,11',
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
            // Start building the query
            $query = CmsArticle::with(['translations', 'banners' => function($query) {
                $query->orderBy('sort_order');
            }])
            ->where('status', 10) // Only published articles
            ->whereDate('publish_date', '<=', Carbon::now()); // Only articles with publish date in the past or today

            // Search in title and descriptions across translations
            if ($request->filled('search')) {
                $search = strtolower($request->search);

                $query->whereHas('translations', function($q) use ($search) {
                    $q->where(function($subQ) use ($search) {
                        $subQ->whereRaw('LOWER(title) LIKE ?', ['%' . $search . '%'])
                             ->orWhereRaw('LOWER(short_description) LIKE ?', ['%' . $search . '%'])
                             ->orWhereRaw('LOWER(description) LIKE ?', ['%' . $search . '%']);
                    });
                });
            }

            // Filter by status if provided (though default is published only)
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Order by publish date (newest first)
            $query->orderBy('publish_date', 'desc');

            // Pagination using Laravel's paginate
            $perPage = $request->input('per_page', 10);
            $perPage = min($perPage, 50);

            $articles = $query->paginate($perPage);

            // Get locale (default to 'en')
            $locale = $request->input('locale', 'en');

            // Transform the data for API response
            $articles->getCollection()->transform(function($article) use ($locale) {
                // Format translations as an object with locale keys
                $translations = [];
                foreach ($article->translations as $trans) {
                    $translations[$trans->locale] = [
                        'title' => $trans->title,
                        'slug' => $trans->slug,
                        'short_description' => $trans->short_description,
                        'description' => $trans->description,
                        'meta_title' => $trans->meta_title,
                        'meta_description' => $trans->meta_description,
                        'meta_keywords' => $trans->meta_keywords,
                    ];
                }

                $banners = $article->banners->map(function($banner) {
                    return [
                        'id' => $banner->id,
                        'image_path' => $banner->image_path,
                        'alt_text' => $banner->alt_text,
                        'target_page' => $banner->target_page,
                        'target_page_web' => $banner->target_page_web,
                        'sort_order' => $banner->sort_order,
                    ];
                })->values();

                return [
                    'id' => $article->id,
                    'thumbnail_path' => $article->thumbnail_path,
                    'publish_date' => $article->publish_date ? Carbon::parse($article->publish_date)->format('Y-m-d') : null,
                    'translations' => $translations,
                    'banners_count' => $article->banners->count(),
                    'banners' => $banners,
                    'created_at' => $article->created_at,
                    'updated_at' => $article->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $articles->items(),
                'pagination' => [
                    'current_page' => $articles->currentPage(),
                    'last_page' => $articles->lastPage(),
                    'per_page' => $articles->perPage(),
                    'total' => $articles->total(),
                    'from' => $articles->firstItem(),
                    'to' => $articles->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching articles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public static function oneArticle($request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:cms_articles,id',
            'locale' => 'nullable|string|in:en,zh_tw,id,zh_cn,ja,ko',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found',
                'errors' => $validator->errors()
            ], 404);
        }

        try {
            $article = CmsArticle::with(['translations', 'banners' => function($query) {
                $query->orderBy('sort_order');
            }])
                ->where('id', $request->id)
                ->where('status', 10) // Only published articles
                ->first();

            if (!$article) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article not found or not published'
                ], 404);
            }

            // Format translations as an object with locale keys
            $translations = [];
            foreach ($article->translations as $trans) {
                $translations[$trans->locale] = [
                    'title' => $trans->title,
                    'slug' => $trans->slug,
                    'short_description' => $trans->short_description,
                    'description' => $trans->description,
                    'meta_title' => $trans->meta_title,
                    'meta_description' => $trans->meta_description,
                    'meta_keywords' => $trans->meta_keywords,
                ];
            }

            // Format banners
            $banners = $article->banners->map(function($banner) {
                return [
                    'id' => $banner->id,
                    'image_path' => $banner->image_path,
                    'alt_text' => $banner->alt_text,
                    'target_page' => $banner->target_page,
                    'target_page_web' => $banner->target_page_web,
                    'sort_order' => $banner->sort_order,
                ];
            })->values();

            $data = [
                'id' => $article->id,
                'thumbnail_path' => $article->thumbnail_path,
                'publish_date' => $article->publish_date ? Carbon::parse($article->publish_date)->format('Y-m-d') : null,
                'translations' => $translations,
                'banners' => $banners,
                'banners_count' => $article->banners->count(),
                'created_at' => $article->created_at,
                'updated_at' => $article->updated_at,
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching article',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
