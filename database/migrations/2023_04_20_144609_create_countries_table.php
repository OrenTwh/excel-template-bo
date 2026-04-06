<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->text('country_name');
            $table->string('country_image',100)->nullable();
            $table->string('currency_symbol',10)->nullable();
            $table->string('iso_alpha2_code',2)->nullable();
            $table->string('iso_alpha3_code',3)->nullable();
            $table->string('calling_code',5);
            $table->tinyInteger('status')->default(20);
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
        Schema::dropIfExists('countries');
    }
}
