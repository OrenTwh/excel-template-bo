<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stores manually-entered daily values for the master sheet left section.
     * Columns C–L in the original spreadsheet (currency amounts per day).
     */
    public function up(): void
    {
        Schema::create('fx_master_daily', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('year');
            $table->tinyInteger('month');
            $table->tinyInteger('day');
            $table->decimal('sgd',      15, 4)->default(0);
            $table->decimal('thb',      15, 4)->default(0);
            $table->decimal('pgk',      15, 4)->default(0);
            $table->decimal('usdt',     15, 4)->default(0);
            $table->decimal('aud_slow', 15, 4)->default(0);
            $table->decimal('aud_fast', 15, 4)->default(0);
            $table->decimal('usd1',     15, 4)->default(0);
            $table->decimal('usd2',     15, 4)->default(0);
            $table->decimal('usd3',     15, 4)->default(0);
            $table->decimal('usd4',     15, 4)->default(0);
            // cols O–Q in original (balance, profit, need_pay — manual overrides)
            $table->decimal('balance_myr', 15, 2)->nullable();
            $table->decimal('need_pay',    15, 2)->nullable();
            $table->timestamps();

            $table->unique(['year', 'month', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fx_master_daily');
    }
};
