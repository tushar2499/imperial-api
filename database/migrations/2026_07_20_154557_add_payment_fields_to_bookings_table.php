<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_method')->default('direct')->after('dropping_id');  // 'direct' | 'sslcommerz'
            $table->string('payment_status')->default('unpaid')->after('payment_method'); // 'unpaid' | 'paid' | 'failed'
            $table->string('tran_id')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_status', 'tran_id']);
        });
    }
};
