<?php

namespace App\View\Components;

use Illuminate\View\Component;

class DataTables extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $enableFilter, $enableFooter, $columns;
    
    public function __construct( $enableFilter, $enableFooter, $columns )
    {
        $this->enableFilter = $enableFilter;
        $this->enableFooter = $enableFooter;
        $this->columns = $columns;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view( 'components.data-tables' );
    }
}
