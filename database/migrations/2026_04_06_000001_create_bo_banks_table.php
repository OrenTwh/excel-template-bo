<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bo_banks', function (Blueprint $table) {
            $table->id();
            $table->string('bank_id')->unique();
            $table->unsignedTinyInteger('display_order')->default(0);
            $table->string('gateway')->nullable();
            $table->string('bank_name');
            $table->string('account_name');
            $table->string('account_number');
            $table->decimal('balance', 15, 2)->default(0);
            $table->text('remark')->nullable();
            $table->json('config')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bo_banks');
    }
};
