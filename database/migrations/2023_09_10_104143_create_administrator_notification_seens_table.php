<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdministratorNotificationSeensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('administrator_notification_seens', function (Blueprint $table) {
            $table->id();
            $table->foreignId( 'an_id' )->constrained( 'administrator_notifications' )->onUpdate( 'restrict' )->onDelete( 'cascade' );
            $table->foreignId( 'a_id' )->constrained( 'administrators' )->onUpdate( 'restrict' )->onDelete( 'cascade' );
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
        Schema::dropIfExists('administrator_notification_seens');
    }
}
