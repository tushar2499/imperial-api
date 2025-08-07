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
            $table->integer('floor')->nullable()->after('name');
            $table->integer('rows')->nullable()->change();
            $table->integer('cols')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seat_plans', function (Blueprint $table) {
            $table->dropColumn('floor');
            $table->integer('rows')->nullable(false)->change();
            $table->integer('cols')->nullable(false)->change();
        });
    }
};
