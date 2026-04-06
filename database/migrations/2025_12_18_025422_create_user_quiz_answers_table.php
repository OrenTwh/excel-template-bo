<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserQuizAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_quiz_id')->constrained()->onDelete('cascade');
            $table->foreignId('field_item_group_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('question_type')->nullable(); // parts, nutrients, health_benefits, product_uses, interesting_facts
            $table->json('user_selection')->nullable(); // The value, array, or sequence they selected
            $table->boolean('is_correct')->default(false);
            $table->integer('points')->default(0);
            $table->tinyInteger('status')->default(10);
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
        Schema::dropIfExists('user_quiz_answers');
    }
}
