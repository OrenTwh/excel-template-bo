<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFieldItemGroupImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('field_item_group_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId( 'field_item_group_id' )->constrained( 'field_item_groups' )->cascadeOnDelete();
        
            $table->string( 'icon' )->nullable();
            $table->string( 'image' )->nullable();
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
        Schema::dropIfExists('field_item_group_images');
    }
}
