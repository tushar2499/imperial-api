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
        Schema::table('coaches', function (Blueprint $table) {
            $table->dropColumn('registration_number');
            $table->dropColumn('manufacturer_company');
            $table->dropColumn('model_year');
            $table->dropColumn('chasis_no');
            $table->dropColumn('engine_number');
            $table->dropColumn('country_of_origin');
            $table->dropColumn('lc_code_number');
            $table->dropColumn('delivery_to_dipo');
            $table->dropColumn('delivery_date');
            $table->dropColumn('color');
            $table->dropColumn('financed_by');
            $table->dropColumn('tennure_of_the_terms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coaches', function (Blueprint $table) {
            $table->string('registration_number')->nullable();
            $table->string('manufacturer_company')->nullable();
            $table->integer('model_year')->nullable();
            $table->string('chasis_no')->nullable();
            $table->string('engine_number')->nullable();
            $table->string('country_of_origin')->nullable();
            $table->string('lc_code_number')->nullable();
            $table->string('delivery_to_dipo')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('color')->nullable();
            $table->string('financed_by')->nullable();
            $table->integer('tennure_of_the_terms')->nullable();
        });
    }
};
