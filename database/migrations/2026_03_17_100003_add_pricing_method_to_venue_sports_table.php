<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venue_sports', function (Blueprint $table) {
            $table->string('pricing_method')->nullable()->after('sport_id');
        });
    }

    public function down(): void
    {
        Schema::table('venue_sports', function (Blueprint $table) {
            $table->dropColumn('pricing_method');
        });
    }
};
