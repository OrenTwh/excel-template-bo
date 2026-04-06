<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFieldActivityOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('field_activity_order_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId( 'field_activity_order_id' )
                ->constrained()
                ->onDelete( 'cascade' );
        
            $table->foreignId( 'field_activity_id' )
                ->constrained()
                ->onDelete( 'cascade' );
        
            $table->integer( 'qty' )->default( 1 );
        
            $table->decimal( 'price', 10, 2 )->nullable();
            $table->decimal( 'subtotal', 10, 2 )->nullable();
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
        Schema::dropIfExists('field_activity_order_details');
    }
}
