<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDistrictsTable extends Migration
{
    public function up()
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->tinyInteger('status')->default(1);  // 1: active, 0: inactive, 2: deleted
            $table->timestamps();
            $table->softDeletes();  // This adds deleted_at for soft deletes
        });
    }

    public function down()
    {
        Schema::dropIfExists('districts');
    }
}
