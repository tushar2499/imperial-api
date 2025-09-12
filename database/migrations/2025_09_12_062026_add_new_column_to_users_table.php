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
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('sales_status')->nullable()->comment('0: No, 1: Yes')->after('password');
            $table->tinyInteger('booking_status')->nullable()->comment('0: No, 1: Yes')->after('sales_status');
            $table->tinyInteger('block_status')->nullable()->comment('0: No, 1: Yes')->after('booking_status');
            $table->tinyInteger('cancel_status')->nullable()->comment('0: No, 1: Yes')->after('block_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('sales_status');
            $table->dropColumn('booking_status');
            $table->dropColumn('block_status');
            $table->dropColumn('cancel_status');
        });
    }
};
