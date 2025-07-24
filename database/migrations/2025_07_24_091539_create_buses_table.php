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
        Schema::create('buses', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number');
            $table->string('manufacturer_company');
            $table->integer('model_year');
            $table->string('chasis_no');
            $table->string('engine_number');
            $table->string('country_of_origin')->nullable();
            $table->string('lc_code_number')->nullable();
            $table->string('delivery_to_dipo')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('color')->nullable();
            $table->string('financed_by')->nullable();
            $table->integer('tennure_of_the_terms')->nullable();
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
        Schema::dropIfExists('buses');
    }
};
