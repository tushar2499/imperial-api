<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY status TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0: inactive, 1: active'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY status ENUM('0','1') NOT NULL DEFAULT '1' COMMENT '0: inactive, 1: active'");
    }
};
