<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    AdministratorService,
};

use Illuminate\Support\Facades\{
    DB,
};

class OwnerController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.owners' );
        $this->data['content'] = 'admin.owner.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.owners' ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.owners' ) ) ] );
        $this->data['content'] = 'admin.owner.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.owner.index' ),
                'text' => __( 'template.owners' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.owners' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $roles = [];
        foreach( DB::table( 'roles' )->select( 'id', 'name' )->orderBy( 'id', 'ASC' )->get() as $role ) {
            $roles[] = [ 'key' => $role->name, 'value' => $role->id, 'title' => __( 'role.' . $role->name ) ];
        }
        $this->data['data']['roles'] = $roles;

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.owners' ) ) ] );
        $this->data['content'] = 'admin.owner.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.owner.index' ),
                'text' => __( 'template.owners' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.owners' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allOwners( Request $request ) {
        return AdministratorService::allOwners( $request );
    }

    public function oneOwner( Request $request ) {
        return AdministratorService::oneOwner( $request );
    }

    public function createOwner( Request $request ) {
        return AdministratorService::createOwner( $request );
    }

    public function updateOwner( Request $request ) {
        return AdministratorService::updateOwner( $request );
    }
}
