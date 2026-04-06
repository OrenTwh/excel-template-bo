<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BlogService;
use App\Services\FileService;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $this->data['header']['title'] = __('Blogs');
        $this->data['content'] = 'admin.blog.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route('admin.dashboard'),
                'text' => __('template.dashboard'),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __('Blogs'),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __('datatables.activated'),
            '20' => __('datatables.suspended'),
        ];

        return view('admin.main')->with($this->data);
    }

    public function add(Request $request)
    {
        $this->data['header']['title'] = __('template.add_x', ['title' => 'Sport']);
        $this->data['content'] = 'admin.blog.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route('admin.dashboard'),
                'text' => __('template.dashboard'),
                'class' => '',
            ],
            [
                'url' => route('admin.module_parent.blog.index'),
                'text' => __('Blogs'),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __('template.add_x', ['title' => 'Sport']),
                'class' => 'active',
            ],
        ];

        return view('admin.main')->with($this->data);
    }

    public function edit(Request $request)
    {
        $this->data['header']['title'] = __('template.edit_x', ['title' => 'Sport']);
        $this->data['content'] = 'admin.blog.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route('admin.dashboard'),
                'text' => __('template.dashboard'),
                'class' => '',
            ],
            [
                'url' => route('admin.module_parent.blog.index'),
                'text' => __('Blogs'),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __('template.edit_x', ['title' => 'Sport']),
                'class' => 'active',
            ],
        ];

        return view('admin.main')->with($this->data);
    }

    public function allBlogs( Request $request ) {

        return BlogService::allBlogs( $request );
    }

    public function oneBlog( Request $request ) {
        return BlogService::oneBlog( $request );
    }

    public function createBlog( Request $request ) {

        return BlogService::createBlog( $request );
    }

    public function updateBlog( Request $request ) {

        return BlogService::updateBlog( $request );
    }

    public function updateBlogStatus( Request $request ) {
        
        return BlogService::updateBlogStatus( $request );
    }

    public function ckeUpload( Request $request ) {

        $request->merge( [ 'path' => 'blogs' ] );
        return FileService::ckeUpload( $request );
    }

    public function imageUpload( Request $request ) {

        $request->merge( [ 'path' => 'blogs' ] );
        return FileService::imageUpload( $request );
    }

}
