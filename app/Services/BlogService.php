<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    Config,
    DB,
    Validator,
    Storage,
};

use Helper;

use App\Models\{
    Blog,
    BlogTag,
    BlogGallery,
};

use Barryvdh\DomPDF\Facade\Pdf;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use Carbon\Carbon;

class BlogService
{

    public static function createBlog( $request ) {
    
        $validator = Validator::make( $request->all(), [
            'meta_title' => [ 'required' ],
            'meta_desc' => [ 'required' ],
            'en_image' => [ 'required' ],
            'slug' => [ 'nullable' ],
            'type' => [ 'nullable' ],
            'publish_date' => [ 'nullable' ],
        ] );

        $attributeName = [
            'meta_title' => __( 'blog.meta_title' ),
            'meta_desc' => __( 'blog.meta_desc' ),
            'en_image' => __( 'blog.image' ),
            'slug' => __( 'blog.slug' ),
            'type' => __( 'blog.type' ),
            'publish_date' => __( 'blog.publish_date' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $multiLangDescription = [];
            $multiLangTitle = [];
            $multiLangImage = [];
            $multiLangSubtitle = [];
            $languages = array_keys( Config::get( 'languages' ) );
            foreach( $languages as $lang ) {
                $key = $lang . '_description';
                if ( $request->filled( $key ) ) {
                    $multiLangDescription[$lang] = $request->$key;
                }
                
                $key = $lang . '_title';
                if ( $request->filled( $key ) ) {
                    $multiLangTitle[$lang] = $request->$key;
                }

                $key = $lang . '_image';
                if ( $request->filled( $key ) ) {
                    $multiLangImage[$lang] = $request->$key;
                }

                $key = $lang . '_subtitle';
                if ( $request->filled( $key ) ) {
                    $multiLangSubtitle[$lang] = $request->$key;
                }
            }

            $blogCreate = Blog::create([
                'meta_title' => $request->meta_title,
                'meta_desc' => $request->meta_desc,
                'publish_date' => $request->publish_date,
                'type' => $request->type,
                'slug' => $request->slug,
                'multi_lang_image' => json_encode( $multiLangImage, JSON_UNESCAPED_UNICODE ),
                'multi_lang_description' => json_encode( $multiLangDescription, JSON_UNESCAPED_UNICODE ),
                'multi_lang_title' => json_encode( $multiLangTitle, JSON_UNESCAPED_UNICODE ),
                'multi_lang_subtitle' => json_encode( $multiLangSubtitle, JSON_UNESCAPED_UNICODE ),
            ]);
            
            if( !empty( $request->tag ) ) {
                if ( str_contains( $request->tag, ',' ) ) {
                    $tags = explode( ',', $request->tag );
                    foreach( $tags as $tag ) {
                        $createBlogTag = BlogTag::create( [
                            'blog_id' => $blogCreate->id,
                            'tag' => $tag,
                        ] );
                    }
                }else{
                    $createBlogTag = BlogTag::create( [
                        'blog_id' => $blogCreate->id,
                        'tag' => $request->tag,
                    ] );
                }
            }

            foreach ( $languages as $lang ) {
                $key = $lang . '_gallery';
                if ( $request->filled( $key ) ) {
                    $gallery = json_decode( $request->$key, true );
                    foreach ( $gallery as $image ) {
                        $blogImage = BlogGallery::create( [
                            'blog_id' => $blogCreate->id,
                            'lang' => $lang,
                            'image' => $image,
                        ] );
                    }
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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.blogs' ) ) ] ),
        ] );
    }
    
    public static function updateBlog($request) {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        $languages = array_keys(Config::get('languages'));

        $validator = Validator::make( $request->all(), [
            'meta_title' => [ 'required' ],
            'meta_desc' => [ 'required' ],
            'en_image' => [ 'required' ],
            'slug' => [ 'nullable' ],
            'type' => [ 'nullable' ],
            'publish_date' => [ 'nullable' ],
        ] );

        $attributeName = [
            'meta_title' => __( 'blog.meta_title' ),
            'meta_desc' => __( 'blog.meta_desc' ),
            'en_image' => __( 'blog.image' ),
            'slug' => __( 'blog.slug' ),
            'type' => __( 'blog.type' ),
            'publish_date' => __( 'blog.publish_date' ),
        ];

        $validator->setAttributeNames( $attributeName )->validate();
        
        DB::beginTransaction();

        try {
            $blog = Blog::findOrFail($request->id);

            // --- update multi-lang title ---
            $multiLangTitle = json_decode($blog->multi_lang_title, true) ?? [];
            foreach ($languages as $lang) {
                $key = $lang . '_title';
                if ($request->filled($key)) {
                    $multiLangTitle[$lang] = $request->$key;
                } else {
                    $multiLangTitle[$lang] = '';
                }
            }
            $blog->multi_lang_title = json_encode($multiLangTitle, JSON_UNESCAPED_UNICODE);

            // --- update multi-lang subtitle ---
            $multiLangSubtitle = json_decode($blog->multi_lang_subtitle, true) ?? [];
            foreach ($languages as $lang) {
                $key = $lang . '_subtitle';
                if ($request->filled($key)) {
                    $multiLangSubtitle[$lang] = $request->$key;
                } else {
                    $multiLangSubtitle[$lang] = '';
                }
            }
            $blog->multi_lang_subtitle = json_encode($multiLangSubtitle, JSON_UNESCAPED_UNICODE);

            // --- update multi-lang description ---
            $multiLangDescription = json_decode($blog->multi_lang_description, true) ?? [];
            foreach ($languages as $lang) {
                $key = $lang . '_description';
                if ($request->filled($key)) {
                    $multiLangDescription[$lang] = $request->$key;
                } else {
                    $multiLangDescription[$lang] = '';
                }
            }
            $blog->multi_lang_description = json_encode($multiLangDescription, JSON_UNESCAPED_UNICODE);

            // --- update multi-lang main image ---
            $multiLangImage = json_decode($blog->multi_lang_image, true) ?? [];
            foreach ($languages as $lang) {
                $key = $lang . '_image';
                if ($request->filled($key)) {
                    $multiLangImage[$lang] = $request->$key;
                } else {
                    $multiLangImage[$lang] = '';
                }
            }
            $blog->multi_lang_image = json_encode($multiLangImage, JSON_UNESCAPED_UNICODE);

            // --- update other blog fields ---
            $blog->meta_title = $request->meta_title;
            $blog->meta_desc = $request->meta_desc;
            $blog->publish_date = $request->publish_date;
            $blog->type = $request->type;
            $blog->slug = $request->slug;
            $blog->save();

            // --- UPDATE GALLERY PER LANGUAGE ---
            foreach ($languages as $lang) {
                $key = $lang . '_gallery';
                $gallery = $request->filled($key) ? json_decode($request->$key, true) : [];

                // Get current gallery images for that language
                $currentImages = BlogGallery::where('blog_id', $blog->id)
                    ->where('lang', $lang)
                    ->where('status', 10)
                    ->pluck('image')
                    ->toArray();

                // Mark deleted ones
                $deletedImages = array_diff($currentImages, $gallery);
                if (!empty($deletedImages)) {
                    BlogGallery::where('blog_id', $blog->id)
                        ->where('lang', $lang)
                        ->whereIn('image', $deletedImages)
                        ->update(['status' => 20]);
                }

                // Add new ones
                $newImages = array_diff($gallery, $currentImages);
                foreach ($newImages as $image) {
                    BlogGallery::create([
                        'blog_id' => $blog->id,
                        'lang' => $lang,
                        'image' => $image,
                        'status' => 10,
                    ]);
                }
            }

            $deleteTag = BlogTag::where( 'blog_id', $blog->id )
                ->get();
            foreach( $deleteTag as $delete ) {
                $delete->delete();
            }

            if( !empty( $request->tag ) ) {
                if ( str_contains( $request->tag, ',' ) ) {
                    $tags = explode( ',', $request->tag );
                    foreach( $tags as $tag ) {
                        $createBlogTag = BlogTag::create( [
                            'blog_id' => $blog->id,
                            'tag' => $tag,
                        ] );
                    }
                }else{
                    $createBlogTag = BlogTag::create( [
                        'blog_id' => $blog->id,
                        'tag' => $request->tag,
                    ] );
                }
            }

            DB::commit();

            return response()->json([
                'message' => __('template.x_updated', ['title' => Str::singular(__('template.blogs'))]),
            ]);

        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }
    }

    public static function allBlogs( $request ) {

        $blogs = Blog::with( [
            'gallery',
            'tags',
        ] )->select( 'blogs.*' )->where( 'type', $request->type );

        $filterObject = self::filter( $request, $blogs );
        $blog = $filterObject['model'];
        $filter = $filterObject['filter'];

        $blog->orderBy( 'blogs.created_at', 'DESC' );

        $blogCount = $blog->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $blogs = $blog->skip( $offset )->take( $limit )->get();

        foreach ( $blogs as $v ) {
            $v->append( [
                'encrypted_id',
                'image_path',
                'display_publish_date',
            ] );

            if( $v->gallery ) {
                $v->gallery->append( [
                    'encrypted_id',
                    'image_path',
                ] );
            }
        }

        $totalRecord = Blog::where( 'type', $request->type )->count();

        $data = [
            'blogs' => $blogs,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $blogCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );

    }

    private static function filter( $request, $model ) {

        $filter = false;
        if (  !empty( $request->created_date ) ) {
            if ( str_contains( $request->created_date, 'to' ) ) {
                $dates = explode( ' to ', $request->created_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'blogs.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );                
            } else {

                $dates = explode( '-', $request->created_date );
    
                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'blogs.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }

            $filter = true;
        }

        // if( !empty( $request->type ) ){
        //     $model->where( 'blogs.type', $request->type );
        //     $filter = true;
        // }
        if ( !empty( $request->id ) ) {
            $model->where( 'blogs.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }
        
        if ( !empty( $request->status ) ) {
            $model->where( 'blogs.status', $request->status );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneBlog( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $blog = Blog::with( [
            'allGallery',
            'tags',
        ] )->select( 'blogs.*' )->find( $request->id );

        if ( $blog ) {
            $blog->append( [
                'encrypted_id',
                'image_path',
            ] );

            if( $blog->allGallery ) {
                $blog->allGallery->append( [
                    'encrypted_id',
                    'image_path',
                ] );
            }
        }
        
        return response()->json( $blog );
    }

    public static function updateBlogStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateBlog = Blog::find( $request->id );
            $updateBlog->status = $updateBlog->status == 10 ? 20 : 10;

            $updateBlog->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'blog' => $updateBlog,
                    'message_key' => 'update_blog_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_blog_failed',
            ], 500 );
        }
    }

    public static function getBlogs( $request ) {
        $now = Carbon::now();

        $blogs = Blog::with( [
            'gallery',
            'tags',
        ] )->select( 'blogs.*' )
        ->where( 'type', $request->type )
        ->whereDate( 'publish_date', '<=', $now )
        ->where( 'status', 10 );

        $filterObject = self::filter( $request, $blogs );
        $blog = $filterObject['model'];
        $filter = $filterObject['filter'];

        $blog->orderBy( 'publish_date', 'DESC' );

        $blogCount = $blog->count();

        $limit = $request->length ?? 10;
        $offset = $request->start ?? 0;

        $blogs = $blog->skip( $offset )->take( $limit )->get();

        foreach ( $blogs as $p ) {

            $p->append( [
                'encrypted_id',
                'image_path'
            ] );

            if( $p->gallery ) {
                $p->gallery->append( [
                    'encrypted_id',
                    'image_path',
                ] );
            }
        }

        $totalRecord = Blog::whereDate( 'publish_date', '<=', $now )
            ->where( 'status', 10 )
            ->where( 'type', $request->type )
            ->count();
        
        $data = [
            'blogs' => $blogs,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $blogCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json([
            'message' => '',
            'message_key' => 'get_menu_success',
            'data' => $data,
        ]);

    }
    
    public static function getOneBlog( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $blog = Blog::with( [
            'gallery',
            'tags',
        ] )->select( 'blogs.*' )->find( $request->id );

        if ( $blog ) {
            $blog->append( [
                'encrypted_id',
                'image_path',
            ] );

            if( $blog->gallery ) {
                $blog->gallery->append( [
                    'encrypted_id',
                    'image_path',
                ] );
            }
        }
        
        return response()->json( $blog );
    }
    
    public static function oneBlogBySlug( $request ) {

        $blog = Blog::with( [
            'gallery',
            'tags',
        ] )->where( 'slug', $request->slug )->first();
        
        if ( $blog ) {
            $blog->append( [
                'encrypted_id',
                'image_path',
            ] );

            if( $blog->gallery ) {
                $blog->gallery->append( [
                    'encrypted_id',
                    'image_path',
                ] );
            }
        }

        return response()->json( $blog );
    }
}