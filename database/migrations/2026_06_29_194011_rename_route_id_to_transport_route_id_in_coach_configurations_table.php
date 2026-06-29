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
        Schema::table('coach_configurations', function (Blueprint $table) {
            $table->renameColumn('route_id', 'transport_route_id');
        });
    }

    public function down(): void
    {
        Schema::table('coach_configurations', function (Blueprint $table) {
            $table->renameColumn('transport_route_id', 'route_id');
        });
    }
};
