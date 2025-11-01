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
        Schema::create('offer_and_promos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('expired_date')->nullable();
            $table->text('description')->nullable();
            $table->string('link')->nullable();
            $table->string('image');
            $table->tinyInteger('status')->default(1)->comment('1:active, 0:inactive');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_and_promos');
    }
};
