<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFieldActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('field_activities', function (Blueprint $table) {
            $table->id();
            $table->string( 'slug' )->unique();          // like ciku-quiz
            $table->string( 'name' );                    // localized in translations later?
            $table->text( 'description' )->nullable();   // optional details
            $table->decimal('price', 10, 2);
            $table->boolean( 'active' )->default( true );
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
        Schema::dropIfExists('field_activities');
    }
}
