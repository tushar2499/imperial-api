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
        Schema::table('seat_plans', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1)->after('layout_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seat_plans', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
