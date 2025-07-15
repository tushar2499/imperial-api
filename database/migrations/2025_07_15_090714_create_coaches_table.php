<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoachesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coaches', function (Blueprint $table) {
            $table->id();
            $table->string('coach_no')->unique();
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
            $table->unsignedBigInteger('seat_plan_id');
            $table->enum('coach_type', [1, 2]); // 1: Double Deck, 2: Single Deck
            $table->string('financed_by')->nullable();
            $table->integer('tennure_of_the_terms')->nullable(); // in years
            $table->tinyInteger('status')->default(1)->comment('1:active, 0:inactive');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coaches');
    }
}
