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
        Schema::create('coach_configurations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coach_id');
            $table->unsignedBigInteger('schedule_id');
            $table->unsignedBigInteger('bus_id');
            $table->unsignedBigInteger('seat_plan_id');
            $table->unsignedBigInteger('route_id');
            $table->tinyInteger('coach_type')->comment('1: AC, 2: Non-AC');
            $table->tinyInteger('status')->default(1)->comment('1: Active, 0: Inactive');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();


            // Add indexes for better performance
            $table->index(['coach_id', 'schedule_id']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coach_configurations');
    }
};
