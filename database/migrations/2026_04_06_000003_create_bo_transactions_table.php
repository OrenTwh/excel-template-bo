<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bo_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->string('customer_id')->nullable();
            $table->string('customer_phone')->nullable();
            $table->enum('type', ['active', 'inactive'])->default('active');
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status')->default('pending');
            $table->string('agent_username')->nullable();
            $table->unsignedBigInteger('bo_bank_id')->nullable();
            $table->text('other_info')->nullable();
            $table->timestamp('transacted_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bo_transactions');
    }
};
