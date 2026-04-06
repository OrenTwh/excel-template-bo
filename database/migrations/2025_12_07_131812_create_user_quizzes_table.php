<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserQuizzesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('field_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('visit_id')->nullable()->constrained()->onDelete('cascade');
            $table->boolean('is_completed')->default(false);
            $table->integer('score')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamp('completed_at')->nullable();
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
        Schema::dropIfExists('user_quizzes');
    }
}
