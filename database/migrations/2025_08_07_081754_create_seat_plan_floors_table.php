<?php

use App\Models\SeatPlan;
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
        Schema::create('seat_plan_floors', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(SeatPlan::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('layout_type')->nullable();
            $table->integer('rows');
            $table->integer('cols')->nullable();
            $table->integer('step');
            $table->boolean('is_extra_seat')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seat_plan_floors');
    }
};
