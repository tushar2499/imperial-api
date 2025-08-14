<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

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

        try {
            // Insert a record to get the next auto-increment ID
            $id = DB::table($sequenceTable)->insertGetId([
                'created_at' => now()
            ]);

            return $id;
        } catch (\Exception $e) {
            \Log::error("Failed to generate global unique ID for " . static::class . ": " . $e->getMessage());

            // Fallback: get next available ID by checking all partitions
            return static::getNextAvailableIdFallback();
        }
    }

    /**
     * Get the sequence table name for this model
     * This method should be overridden in each model that uses this trait
     *
     * @return string
     */
    protected static function getSequenceTableName(): string
    {
        // Default sequence table name - override this in your models
        $className = class_basename(static::class);
        return strtolower($className) . '_sequences';
    }

    /**
     * Fallback method to get next available ID by checking all partitions
     *
     * @return int
     */
    protected static function getNextAvailableIdFallback(): int
    {
        $model = new static;
        $maxId = 0;

        try {
            // If the model uses partitions, check all partitions
            if (method_exists($model, 'getAllPartitionTables')) {
                $partitions = $model->getAllPartitionTables();

                foreach ($partitions as $partition) {
                    try {
                        $partitionMaxId = DB::table($partition)->max('id') ?? 0;
                        $maxId = max($maxId, $partitionMaxId);
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            } else {
                // For non-partitioned tables
                $maxId = DB::table($model->getTable())->max('id') ?? 0;
            }
        } catch (\Exception $e) {
            \Log::error("Failed to get max ID for fallback: " . $e->getMessage());
            $maxId = 0;
        }

        return $maxId + 1;
    }

    /**
     * Check if ID exists across all partitions
     *
     * @param int $id
     * @return bool
     */
    public static function idExistsGlobally(int $id): bool
    {
        $model = new static;

        try {
            // If the model uses partitions, check all partitions
            if (method_exists($model, 'getAllPartitionTables')) {
                $partitions = $model->getAllPartitionTables();

                foreach ($partitions as $partition) {
                    try {
                        $exists = DB::table($partition)->where('id', $id)->exists();
                        if ($exists) {
                            return true;
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            } else {
                // For non-partitioned tables
                return DB::table($model->getTable())->where('id', $id)->exists();
            }
        } catch (\Exception $e) {
            \Log::error("Failed to check if ID exists globally: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Boot method to auto-assign global IDs
     * This is automatically called when the trait is used
     */
    protected static function bootGlobalUniqueId()
    {
        static::creating(function ($model) {
            // Only assign ID if it's not already set
            if (!$model->getKey()) {
                $model->setAttribute($model->getKeyName(), static::getNextGlobalId());
            }
        });
    }

    /**
     * Override the incrementing property
     * This tells Laravel not to expect auto-incrementing IDs
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * Ensure key type is integer
     *
     * @return string
     */
    public function getKeyType()
    {
        return 'int';
    }
}
