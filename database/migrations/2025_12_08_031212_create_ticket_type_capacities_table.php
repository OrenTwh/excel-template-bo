<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketTypeCapacitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_type_capacities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_type_id')->constrained('ticket_types')->onDelete('cascade');
            $table->integer('weekday_capacity')->default(0);
            $table->integer('weekend_capacity')->default(0);
            $table->tinyInteger( 'status' )->default(10);
            $table->timestamps();

            $table->unique('ticket_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ticket_type_capacities');
    }
}
