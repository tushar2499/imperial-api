<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seat_requests', function (Blueprint $table) {
            // Drop existing indexes that depend on user_id being NOT NULL before altering the column.
            // Use tryMethod so it doesn't fail if an index was already removed or named differently.
            $table->dropIndex(['user_id']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['issue_id', 'user_id']);
            $table->dropIndex(['user_id', 'status', 'created_at']);

            // Allow guest holds — user_id is now nullable
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // Guest ownership token (client-generated UUID, max 64 chars)
            $table->string('guest_token', 64)->nullable()->after('user_id');

            // Recreate indexes with nullable-safe definitions
            $table->index('user_id');
            $table->index(['user_id', 'status']);
            $table->index(['issue_id', 'user_id']);
            $table->index(['user_id', 'status', 'created_at']);
            $table->index('guest_token');
            $table->index(['issue_id', 'guest_token']);
        });
    }

    public function down(): void
    {
        Schema::table('seat_requests', function (Blueprint $table) {
            $table->dropIndex(['guest_token']);
            $table->dropIndex(['issue_id', 'guest_token']);
            $table->dropColumn('guest_token');
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
