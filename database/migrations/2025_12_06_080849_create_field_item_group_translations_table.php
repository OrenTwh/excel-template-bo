<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFieldItemGroupTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('field_item_group_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId( 'field_item_group_id' )->constrained( 'field_item_groups' )->cascadeOnDelete();
        
            $table->string( 'locale' );
            $table->string( 'text' )->nullable();
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
        Schema::dropIfExists('field_item_group_translations');
    }
}
