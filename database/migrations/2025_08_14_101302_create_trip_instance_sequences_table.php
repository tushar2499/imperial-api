<?php

// Create a migration for global sequence table
// php artisan make:migration create_trip_instance_sequences_table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripInstanceSequencesTable extends Migration
{
    public function up()
    {
        Schema::create('trip_instance_sequences', function (Blueprint $table) {
            $table->id(); // This will be our global sequence
            $table->timestamp('created_at')->useCurrent();
        });

        // Create similar table for seat inventories
        Schema::create('seat_inventory_sequences', function (Blueprint $table) {
            $table->id(); // This will be our global sequence
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('trip_instance_sequences');
        Schema::dropIfExists('seat_inventory_sequences');
    }
}

// Trait for generating global unique IDs
trait GlobalUniqueId
{
    /**
     * Get next global unique ID for the model
     *
     * @return int
     */
    public static function getNextGlobalId(): int
    {
        $sequenceTable = static::getSequenceTableName();

        // Insert a record to get the next auto-increment ID
        $id = DB::table($sequenceTable)->insertGetId([
            'created_at' => now()
        ]);

        return $id;
    }

    /**
     * Get the sequence table name for this model
     *
     * @return string
     */
    protected static function getSequenceTableName(): string
    {
        // Override this in each model
        return 'global_sequences';
    }

    /**
     * Boot method to auto-assign global IDs
     */
    protected static function bootGlobalUniqueId()
    {
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = static::getNextGlobalId();
            }
        });
    }
}
