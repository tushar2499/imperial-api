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
        Schema::table('fares', function (Blueprint $table) {
            $table->enum('seat_type', ['Suite Class', 'Business Class', 'Sleeper', 'Economy'])
                  ->after('coach_type')
                  ->default('Economy')
                  ->comment('Suite Class, Business Class, Sleeper, Economy');

            // Add index for better performance on seat_type queries
            $table->index(['seat_type'], 'fares_seat_type_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fares', function (Blueprint $table) {
            $table->dropIndex('fares_seat_type_index');
            $table->dropColumn('seat_type');
        });
    }
};
