<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlogGalleriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blog_galleries', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger( 'blog_id' );
            $table->string( 'lang', 5 ); // en, zh, ms, etc
            $table->string( 'image' );
        
            $table->foreign( 'blog_id' )
                ->references( 'id' )
                ->on( 'blogs' )
                ->onDelete( 'cascade' );
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
        Schema::dropIfExists('blog_galleries');
    }
}
