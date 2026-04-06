<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    CMSService,
};

use App\Models\{
    CmsArticle,
};

use Helper;

class CMSController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = 'CMS Articles';
        $this->data['content'] = 'admin.cms_article.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.projects' ),
                'class' => 'active',
            ],
        ];
        
        $this->data['data']['status'] = [
            '11' => 'Draft',
            '10' => 'Published',
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = 'Add CMS Article';
        $this->data['content'] = 'admin.cms_article.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.cms_article.index' ),
                'text' => 'CMS Articles',
                'class' => '',
            ],
            [
                'url' => '',
                'text' => 'Add CMS Article',
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = 'Edit CMS Article';
        $this->data['content'] = 'admin.cms_article.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.cms_article.index' ),
                'text' => 'CMS Articles',
                'class' => '',
            ],
            [
                'url' => '',
                'text' => 'Edit CMS Article',
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allProjects( Request $request ) {

        return CMSService::allProjects( $request );
    }

    public function oneProject( Request $request ) {

        return CMSService::oneProject( $request );
    }

    public function createProject( Request $request ) {

        return CMSService::createProject( $request );
    }

    public function updateProject( Request $request ) {

        return CMSService::updateProject( $request );
    }

    public function updateProjectStatus ( Request $request ) {

        return CMSService::updateProjectStatus ( $request );
    }

    public function deleteProject( Request $request ) {

        return CMSService::deleteProject( $request );
    }

    public function ckeUpload( Request $request ) {

        return CMSService::ckeUpload( $request );
    }

    public function removeThumbnail( Request $request ) {

        return CMSService::removeThumbnail( $request );
    }

    public function deleteBanner( Request $request ) {

        return CMSService::deleteBanner( $request );
    }

}