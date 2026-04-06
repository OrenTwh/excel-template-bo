<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable();
            $table->string('fullname')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('facebook_name')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('calling_code')->nullable();
            $table->string('phone_number')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postcode')->nullable();
            $table->tinyInteger('account_type')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->tinyInteger('is_social_account')->nullable()->default(0);
            $table->tinyInteger('platform')->nullable()->default(1);
            $table->string( 'profile_picture' )->nullable();
            $table->string('status')->default(10);
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
