<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTmpUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tmp_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained('countries')->onUpdate('restrict')->onDelete('cascade');
            $table->string('phone_number', 20)->nullable();
            $table->string('calling_code', 20)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('otp_code', 6);
            $table->tinyInteger('status');
            $table->timestamp('expire_on');
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
        Schema::dropIfExists('tmp_users');
    }
}
