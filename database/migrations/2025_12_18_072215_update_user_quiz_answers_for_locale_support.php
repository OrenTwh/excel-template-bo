<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUserQuizAnswersForLocaleSupport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_quiz_answers', function (Blueprint $table) {
            $table->string('quiz_locale', 5)->default('en')->after('field_item_group_id');
            $table->json('selected_group_ids')->nullable()->after('user_selection');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_quiz_answers', function (Blueprint $table) {
            $table->dropColumn(['quiz_locale', 'selected_group_ids']);
        });
    }
}
