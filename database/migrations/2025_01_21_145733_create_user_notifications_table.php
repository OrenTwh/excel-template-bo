<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId( 'user_id' )->nullable()->constrained( 'users' )->onUpdate( 'restrict' )->onDelete( 'cascade' );
            $table->string( 'title' )->nullable();
            $table->text( 'content' )->nullable();
            $table->string( 'system_title' )->nullable();
            $table->text( 'system_content' )->nullable();
            $table->text( 'meta_data' )->nullable();
            $table->string('url_slug')->nullable();
            $table->string( 'image' )->nullable();
            $table->tinyInteger( 'type' )->default( 1 );
            $table->tinyInteger( 'status' )->default( 10 );
            $table->tinyInteger( 'is_broadcast' )->default( 0 );
            $table->string( 'key' )->nullable();
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
        Schema::dropIfExists('user_notifications');
    }
}
