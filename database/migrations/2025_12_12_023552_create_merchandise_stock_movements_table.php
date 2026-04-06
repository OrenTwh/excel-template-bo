<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchandiseStockMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchandise_stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId( 'merchandise_id' )
                ->constrained()
                ->onDelete( 'cascade' );
        
            $table->enum( 'type', [ 'in', 'out' ] );   // stock in or out
            $table->integer( 'quantity' );             // change amount (+ or -)
            $table->text( 'remark' )->nullable();      // e.g. "manual update", "order #1001"
        
            $table->integer( 'before_quantity' )->nullable();  
            $table->integer( 'after_quantity' )->nullable();
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
        Schema::dropIfExists('merchandise_stock_movements');
    }
}
