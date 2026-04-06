<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('court_bookings', function (Blueprint $table) {
            $table->unsignedInteger('participants')->default(1)->after('duration_hours');
        });
    }

    public function down(): void
    {
        Schema::table('court_bookings', function (Blueprint $table) {
            $table->dropColumn('participants');
        });
    }
};
