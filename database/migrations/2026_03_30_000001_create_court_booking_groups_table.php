<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Create the master group table ──────────────────────────────────
        Schema::create('court_booking_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('group_no')->unique();
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->onDelete('set null');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('payment_status')->default('pending'); // pending, paid, failed, refunded
            $table->string('payment_gateway')->nullable();
            $table->string('payment_gateway_ref')->nullable();
            $table->unsignedTinyInteger('payment_attempt')->default(0);
            $table->tinyInteger('status')->default(1); // 1=pending_payment, 10=upcoming, 11=complete, 20=suspended, 21=canceled
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        // ── 2. Clear existing rows so the new non-nullable FK can be added ───
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('court_bookings')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ── 3. Modify court_bookings to become line-items ─────────────────────
        Schema::table('court_bookings', function (Blueprint $table) {
            // Drop composite index that references user_id before dropping the column
            // $table->dropIndex('court_bookings_user_id_booking_date_index');

            // Drop FK constraints before dropping columns
            $table->dropForeign(['user_id']);
            $table->dropForeign(['voucher_id']);

            // Drop columns that move to the group
            $table->dropColumn([
                'user_id',
                'booking_no',
                'payment_status',
                'payment_gateway',
                'payment_gateway_ref',
                'payment_attempt',
                'booking_status',
                'voucher_id',
                'status',
                'confirmed_at',
                'cancelled_at',
                'cancellation_reason',
            ]);

            // Add FK to the group (after court_id)
            $table->foreignId('court_booking_group_id')
                ->after('id')
                ->constrained('court_booking_groups')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('court_bookings', function (Blueprint $table) {
            $table->dropForeign(['court_booking_group_id']);
            $table->dropColumn('court_booking_group_id');

            // Restore moved columns
            $table->foreignId('user_id')->after('court_id')->constrained('users')->onDelete('cascade');
            $table->string('booking_no')->unique()->after('user_id');
            $table->string('payment_status')->default('pending')->after('total_amount');
            $table->string('payment_gateway')->nullable()->after('payment_status');
            $table->string('payment_gateway_ref')->nullable()->after('payment_gateway');
            $table->unsignedTinyInteger('payment_attempt')->default(0)->after('payment_gateway_ref');
            $table->string('booking_status')->default('pending')->after('payment_attempt');
            $table->foreignId('voucher_id')->nullable()->after('notes')->constrained('vouchers')->onDelete('set null');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('voucher_id');
            $table->tinyInteger('status')->default(10)->after('discount_amount');
            $table->timestamp('confirmed_at')->nullable()->after('status');
            $table->timestamp('cancelled_at')->nullable()->after('confirmed_at');
            $table->string('cancellation_reason')->nullable()->after('cancelled_at');

            $table->index(['user_id', 'booking_date']);
        });

        Schema::dropIfExists('court_booking_groups');
    }
};
