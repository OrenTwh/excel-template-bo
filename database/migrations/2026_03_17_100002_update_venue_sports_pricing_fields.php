<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Make slot-based fields nullable via raw SQL (no doctrine/dbal needed)
        DB::statement('ALTER TABLE venue_sports MODIFY slot_duration INT NULL DEFAULT NULL');
        DB::statement('ALTER TABLE venue_sports MODIFY price_per_slot DECIMAL(10,2) NULL DEFAULT NULL');

        Schema::table('venue_sports', function (Blueprint $table) {
            $table->decimal('price_per_person', 10, 2)->nullable()->after('price_per_slot');
            $table->decimal('price_per_night', 10, 2)->nullable()->after('price_per_person');
        });
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE venue_sports MODIFY slot_duration INT NOT NULL DEFAULT 60');
        DB::statement('ALTER TABLE venue_sports MODIFY price_per_slot DECIMAL(10,2) NOT NULL DEFAULT 0');

        Schema::table('venue_sports', function (Blueprint $table) {
            $table->dropColumn(['price_per_person', 'price_per_night']);
        });
    }
};
