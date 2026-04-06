<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldItemGroupIdToUserQuizzesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_quizzes', function (Blueprint $table) {
            $table->foreignId('field_item_group_id')->nullable()->after('field_item_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_quizzes', function (Blueprint $table) {
            $table->dropForeign(['field_item_group_id']);
            $table->dropColumn('field_item_group_id');
        });
    }
}
