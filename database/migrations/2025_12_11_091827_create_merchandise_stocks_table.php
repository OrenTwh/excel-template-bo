<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchandiseStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchandise_stocks', function (Blueprint $table) {
            $table->id();

            $table->foreignId( 'merchandise_id' )
                ->constrained()
                ->onDelete( 'cascade' );
        
            $table->integer( 'quantity' )->default( 0 );   // current stock
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
        Schema::dropIfExists('merchandise_stocks');
    }
}
