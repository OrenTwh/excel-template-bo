<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFieldItemGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('field_item_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId( 'field_item_id' )->constrained( 'field_items' )->cascadeOnDelete();
        
            $table->string( 'group_type' ); // parts, helps, nutrients, makes, facts
            $table->integer( 'position' )->default( 0 );
            $table->tinyInteger( 'status' )->default(10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('field_item_groups');
    }
}
