<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('court_calendars', function (Blueprint $table) {
            $table->string('external_form_link')->nullable()->after('price_per_participant');
        });
    }

    public function down(): void
    {
        Schema::table('court_calendars', function (Blueprint $table) {
            $table->dropColumn('external_form_link');
        });
    }
};
