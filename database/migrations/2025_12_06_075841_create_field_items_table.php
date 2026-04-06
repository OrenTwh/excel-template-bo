<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFieldItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('field_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string( 'slug' )->unique();
            $table->string( 'type' ); // fruit, flower, tree
            $table->string( 'parts_image' )->nullable();  // parts_image from JSON
            $table->string( 'theme' )->nullable();        // theme color string
            $table->string('image')->nullable();
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
        Schema::dropIfExists('field_items');
    }
}
