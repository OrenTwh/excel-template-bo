<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Columns map (user sheet → DB column):
     *   A  date         → date
     *   B  currency     → currency
     *   C  BUY(IN)      → amount_in
     *   D  SELL(OUT)    → amount_out
     *   E  Rate         → rate
     *   F  CONVERT MYR  → myr_converted  [calculated: E*(C-D)]
     *   G  MYR OUT      → myr_out
     *   H  MYR IN       → myr_in
     *   I  REMARK       → remark
     *   K  COST Rate    → cost_rate
     *   L  PROFIT       → profit          [calculated: C*(K-E)]
     */
    public function up(): void
    {
        Schema::create('fx_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fx_customer_id');
            $table->date('date');
            $table->string('currency', 20)->nullable();
            $table->decimal('amount_in',       15, 4)->default(0);  // BUY IN  (C)
            $table->decimal('amount_out',      15, 4)->default(0);  // SELL OUT (D)
            $table->decimal('rate',            15, 6)->default(0);  // Exchange rate (E)
            $table->decimal('myr_converted',   15, 2)->default(0);  // F = E*(C-D)
            $table->decimal('myr_out',         15, 2)->default(0);  // MYR OUT (G)
            $table->decimal('myr_in',          15, 2)->default(0);  // MYR IN  (H)
            $table->text('remark')->nullable();                      // REMARK  (I)
            $table->decimal('cost_rate',       15, 6)->default(0);  // COST Rate (K)
            $table->decimal('profit',          15, 2)->default(0);  // L = C*(K-E)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('fx_customer_id')->references('id')->on('fx_customers');
            $table->index(['fx_customer_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fx_transactions');
    }
};
