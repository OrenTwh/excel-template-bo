<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDateClosuresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('date_closures', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('reason')->nullable();
            $table->tinyInteger( 'status' )->default(10);
            $table->timestamps();

            $table->unique('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('date_closures');
    }
}
