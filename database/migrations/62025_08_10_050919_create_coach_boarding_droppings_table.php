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
        Schema::create('coach_boarding_droppings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coach_configuration_id');
            $table->unsignedBigInteger('counter_id');
            $table->tinyInteger('type')->comment('1: Boarding, 2: Dropping');
            $table->time('time');
            $table->tinyInteger('starting_point_status')->default(0)->comment('1: Yes, 0: No');
            $table->tinyInteger('ending_point_status')->default(0)->comment('1: Yes, 0: No');
            $table->tinyInteger('status')->default(1)->comment('1: Active, 0: Inactive');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Add indexes for better performance
            $table->index(['coach_configuration_id', 'type']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coach_boarding_droppings');
    }
};
