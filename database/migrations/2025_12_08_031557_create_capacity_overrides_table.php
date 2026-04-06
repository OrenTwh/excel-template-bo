<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCapacityOverridesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('capacity_overrides', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('ticket_type_id')->constrained('ticket_types')->onDelete('cascade');
            $table->integer('capacity')->default(0);
            $table->tinyInteger( 'status' )->default(10);
            $table->timestamps();

            $table->unique(['date', 'ticket_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('capacity_overrides');
    }
}
