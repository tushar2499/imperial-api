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
        Schema::table('seat_requests', function (Blueprint $table) {
            $table->string('tran_id')->nullable()->after('metadata');
        });
    }

    public function down(): void
    {
        Schema::table('seat_requests', function (Blueprint $table) {
            $table->dropColumn('tran_id');
        });
    }
};
