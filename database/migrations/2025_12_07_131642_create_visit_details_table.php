<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisitDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visit_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('visits')->onDelete('cascade');
            $table->foreignId('ticket_type_id')->constrained()->onDelete('restrict');
            $table->integer('quantity')->default(1);
            $table->decimal('total_price', 10, 2);
            $table->tinyInteger( 'status' )->default(10);
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
        Schema::dropIfExists('visit_details');
    }
}
