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
        // Create the base seat_inventories table (template for partitions)
        Schema::create('seat_inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id');
            $table->unsignedBigInteger('seat_id');
            $table->tinyInteger('booking_status')->default(1)->comment('1: Available, 2: Booked, 3: Blocked, 0: Cancelled');
            $table->datetime('blocked_until')->nullable();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('last_locked_user_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();


            // Add indexes for better performance
            $table->index(['trip_id', 'booking_status']);
            $table->index(['seat_id', 'booking_status']);
            $table->index(['booking_status']);
            $table->index(['blocked_until']);
            $table->unique(['trip_id', 'seat_id'], 'unique_trip_seat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seat_inventories');
    }
};
