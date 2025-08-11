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
        // Create the base trip_instances table
        // This serves as the template for partition tables
        Schema::create('trip_instances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coach_id');
            $table->unsignedBigInteger('bus_id');
            $table->unsignedBigInteger('schedule_id');
            $table->unsignedBigInteger('seat_plan_id');
            $table->unsignedBigInteger('route_id');
            $table->tinyInteger('coach_type')->comment('1: AC, 2: Non-AC');
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->unsignedBigInteger('supervisor_id')->nullable();
            $table->date('trip_date');
            $table->tinyInteger('status')->default(1)->comment('1: Active, 0: Inactive, 2: Migrated');
            $table->unsignedBigInteger('migrated_trip_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('migrated_by')->nullable();
            $table->timestamps();

            // Add indexes for better performance
            $table->index(['trip_date', 'status']);
            $table->index(['coach_id', 'trip_date']);
            $table->index(['route_id', 'trip_date']);
            $table->index(['status']);
            $table->unique(['coach_id', 'schedule_id', 'trip_date'], 'unique_coach_schedule_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_instances');
    }
};
