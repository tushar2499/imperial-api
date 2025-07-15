<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('counters', function (Blueprint $table) {
            $table->id();
            $table->enum('type', [1, 2, 3]); // 1: Own Counter, 2: Commission Counter, 3: Head Office
            $table->string('address');
            $table->string('land_mark')->nullable();
            $table->string('location_url')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('primary_contact_no')->nullable();
            $table->string('country')->nullable();
            $table->bigInteger('district_id');
            $table->enum('booking_allowed_status', [1, 2, 3]) // 1: Coach wise, 2: Route wise, 3: Both
                ->default(1);
            $table->enum('booking_allowed_class', [1, 2, 3, 4]) // 1: B Class, 2: E Class, 3: All, 4: Sleeper
                ->default(1);
            $table->integer('no_of_boarding_allowed')->nullable();
            $table->enum('sms_status',[1, 2])->default(true); // Whether SMS is enabled
            $table->enum('status', [1, 0]) // 1: active, 0: inactive
                ->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes(); // for deleted_at column
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('counters');
    }
}
