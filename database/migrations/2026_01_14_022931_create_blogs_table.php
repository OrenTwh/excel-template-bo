<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();

            $table->string( 'meta_title' )->nullable();
            $table->text( 'meta_desc' )->nullable();
        
            $table->dateTime( 'publish_date' )->nullable();
            $table->string( 'type' )->nullable();
            $table->string( 'slug' )->nullable()->unique();
        
            $table->longText( 'multi_lang_image' )->nullable();
            $table->longText( 'multi_lang_description' )->nullable();
            $table->longText( 'multi_lang_title' )->nullable();
            $table->longText( 'multi_lang_subtitle' )->nullable();
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
        Schema::dropIfExists('blogs');
    }
}
