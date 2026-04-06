<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFieldItemTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('field_item_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->nullable()->constrained('field_items')->onUpdate('restrict')->onDelete('cascade');
            $table->string('locale');
            $table->string('name')->nullable();
            $table->string('scientific_name')->nullable();
            $table->string('origin')->nullable();
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
        Schema::dropIfExists('field_item_translations');
    }
}
