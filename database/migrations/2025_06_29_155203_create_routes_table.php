<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id(); // BIGINT for the primary key
            $table->bigInteger('start_id'); // Foreign key to destinations table
            $table->bigInteger('end_id'); // Foreign key to destinations table
            $table->float('distance'); // In kilometers
            $table->string('duration'); // e.g., "06:30"
            $table->enum('status', ['1', '0', '2'])->default('1'); // Status with possible values
            $table->bigInteger('created_by'); // Foreign key to users table for created_by
            $table->bigInteger('updated_by')->nullable(); // Foreign key to users table for updated_by
            $table->timestamps(); // created_at and updated_at
            $table->softDeletes(); // deleted_at for soft deletes
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('routes');
    }
}
