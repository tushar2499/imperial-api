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
        Schema::create('seat_requests', function (Blueprint $table) {
            $table->id();
            $table->string('issue_id')->comment('Issue identifier - can have multiple seats per issue');
            $table->unsignedBigInteger('seat_inventory_id');
            $table->unsignedBigInteger('trip_id');
            $table->unsignedBigInteger('seat_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('status', ['pending', 'expired', 'cancelled', 'booked'])->default('pending');
            $table->timestamp('blocked_until')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable()->comment('Additional data like passenger info, etc.');
            $table->timestamps();

            // Indexes for performance
            $table->index('issue_id');
            $table->index('seat_inventory_id');
            $table->index('trip_id');
            $table->index('seat_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('blocked_until');
            $table->index(['user_id', 'status']);
            $table->index(['trip_id', 'seat_id']);
            $table->index(['issue_id', 'user_id']);

            // Composite index for common queries
            $table->index(['user_id', 'status', 'created_at']);
            $table->index(['issue_id', 'status']);

            // Note: trip_id foreign key would need to reference the partitioned table
            // $table->foreign('trip_id')->references('id')->on('trip_instances')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seat_requests');
    }
};
