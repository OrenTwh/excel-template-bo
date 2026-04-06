<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdministratorNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('administrator_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId( 'module_id' )->nullable();
            $table->string( 'title' )->nullable();
            $table->text( 'content' )->nullable();
            $table->string( 'system_title',50)->nullable();
            $table->string( 'system_content',50)->nullable();
            $table->text( 'meta_data' )->nullable();
            $table->string( 'image' )->nullable();
            $table->string( 'module', '75' )->nullable();
            $table->tinyInteger( 'type' );
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
        Schema::dropIfExists('administrator_notifications');
    }
}
