<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fx_customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // Customer name / sheet name (Y col in master)
            $table->decimal('initial_balance', 15, 2)->default(0); // I1 — opening balance injected manually
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('check_today')->default(false);  // L1 — toggle: show today only
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fx_customers');
    }
};
