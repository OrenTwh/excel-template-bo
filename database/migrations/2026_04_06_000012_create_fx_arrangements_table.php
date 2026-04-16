<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Columns map (user sheet N→U section → DB column):
     *   N  DATE           → date
     *   O  ACCOUNT NUMBER → account_number
     *   P  NAME           → beneficiary_name
     *   Q  BANK           → bank
     *   R  ARRANGING      → arranging_amount
     *   S  DONE AMOUNT    → done_amount
     *   T  DONE           → is_done
     *   U  BY             → processed_by
     */
    public function up(): void
    {
        Schema::create('fx_arrangements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fx_customer_id');
            $table->date('date');
            $table->string('account_number')->nullable();
            $table->string('beneficiary_name')->nullable();
            $table->string('bank')->nullable();
            $table->decimal('arranging_amount', 15, 2)->default(0); // R — amount to arrange
            $table->decimal('done_amount',      15, 2)->default(0); // S — amount done
            $table->boolean('is_done')->default(false);              // T — done flag
            $table->string('processed_by')->nullable();              // U — who processed
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('fx_customer_id')->references('id')->on('fx_customers');
            $table->index(['fx_customer_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fx_arrangements');
    }
};
