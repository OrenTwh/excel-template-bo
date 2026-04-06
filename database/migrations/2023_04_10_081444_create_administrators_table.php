<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdministratorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('administrators', function (Blueprint $table) {
            $table->id();
            $table->string('name',25)->unique();
            $table->string('email',50)->unique()->nullable();
            $table->string('phone_number')->unique();
            $table->string('calling_code')->default('+60');
            $table->string('password');
            $table->string('fullname',100)->nullable();
            $table->text('mfa_secret')->nullable();
            $table->rememberToken();
            $table->tinyInteger('role');
            $table->tinyInteger('status')->comment('10:active 20:suspended');
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
        Schema::dropIfExists('administrators');
    }
}
