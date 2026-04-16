<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bo_bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bo_bank_id');
            $table->date('date');
            $table->text('description')->nullable();
            $table->decimal('amount_in', 15, 2)->nullable();
            $table->decimal('amount_out', 15, 2)->nullable();
            $table->time('time')->nullable();
            $table->string('ref_id')->nullable();
            $table->string('match')->nullable();
            $table->decimal('fee', 15, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->text('info')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('bo_bank_id')->references('id')->on('bo_banks');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bo_bank_transactions');
    }
};
