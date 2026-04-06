<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchandiseOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchandise_order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId( 'merchandise_order_id' )
                ->constrained()
                ->onDelete( 'cascade' );
        
            $table->foreignId( 'merchandise_id' )
                ->constrained()
                ->onDelete( 'cascade' );
        
            $table->integer( 'qty' )->default( 1 );
            $table->decimal( 'price', 10, 2 );        // snapshot at order time
            $table->decimal( 'subtotal', 10, 2 );     // qty × price
        
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
        Schema::dropIfExists('merchandise_order_items');
    }
}
