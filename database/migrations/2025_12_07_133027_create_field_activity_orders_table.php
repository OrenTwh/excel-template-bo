<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFieldActivityOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('field_activity_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId( 'visit_id' )
                ->constrained( 'visits' )
                ->onDelete( 'cascade' );
            $table->string( 'order_no' )->unique()->nullable();
            $table->decimal( 'total_price', 12, 2 )->default( 0 );
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
        Schema::dropIfExists('field_activity_orders');
    }
}
