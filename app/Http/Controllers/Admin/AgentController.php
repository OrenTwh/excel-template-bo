<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    AgentService,
};

class AgentController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.agents' );
        $this->data['content'] = 'admin.agent.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.agents' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.agents' ) ) ] );
        $this->data['content'] = 'admin.agent.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.agent.index' ),
                'text' => __( 'template.agents' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.agents' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.agents' ) ) ] );
        $this->data['content'] = 'admin.agent.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.agent.index' ),
                'text' => __( 'template.agents' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.agents' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allAgents( Request $request ) {

        return AgentService::allAgents( $request );
    }

    public function oneAgent( Request $request ) {

        return AgentService::oneAgent( $request );
    }

    public function createAgent( Request $request ) {

        return AgentService::createAgent( $request );
    }

    public function updateAgent( Request $request ) {

        return AgentService::updateAgent( $request );
    }

    public function updateAgentStatus( Request $request ) {

        return AgentService::updateAgentStatus( $request );
    }

    public function removeAgentProfilePicture( Request $request ) {

        return AgentService::removeAgentProfilePicture( $request );
    }
}
