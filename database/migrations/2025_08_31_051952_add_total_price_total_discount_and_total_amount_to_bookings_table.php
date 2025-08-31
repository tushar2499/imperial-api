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
           $table->decimal('total_price', 11, 3)->default(0)->after('dropping_id');
           $table->decimal('total_discount', 11, 3)->default(0)->after('total_price');
           $table->decimal('total_amount', 11, 3)->default(0)->after('total_discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('total_price');
            $table->dropColumn('total_discount');
            $table->dropColumn('total_amount');
        });
    }
};
