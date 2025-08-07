<?php

use App\Models\SeatPlanFloor;
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
        Schema::table('seats', function (Blueprint $table) {
            $table->foreignIdFor(SeatPlanFloor::class)->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->boolean('is_disable')->default(false)->after('seat_type');
            $table->string('seat_number')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seats', function (Blueprint $table) {
            $table->dropForeign(['seat_plan_floor_id']);
            $table->dropColumn(['seat_plan_floor_id', 'is_disable']);
            $table->string('seat_number')->nullable(false)->change();
        });
    }
};
