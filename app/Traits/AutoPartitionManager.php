<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

trait AutoPartitionManager
{
    /**
     * Get the partition table name for a given date
     *
     * @param string|Carbon $date
     * @return string
     */
    public function getPartitionTableName($date): string
    {
        $carbonDate = $date instanceof Carbon ? $date : Carbon::parse($date);
        $yearMonth = $carbonDate->format('Ym');

        return $this->getTable() . '_' . $yearMonth;
    }

    /**
     * Auto-create partition table if it doesn't exist
     *
     * @param string|Carbon $date
     * @return bool
     */
    public function ensurePartitionExists($date): bool
    {
        $partitionTable = $this->getPartitionTableName($date);

        if (Schema::hasTable($partitionTable)) {
            return true;
        }

        try {
            $baseTable = $this->getTable();

            // First ensure the base table exists
            if (!Schema::hasTable($baseTable)) {
                \Log::error("Base table {$baseTable} does not exist. Cannot create partition.");
                return false;
            }

            // Get the CREATE TABLE statement from the base table
            $result = DB::select("SHOW CREATE TABLE `{$baseTable}`");

            if (empty($result)) {
                \Log::error("Could not get CREATE TABLE statement for {$baseTable}");
                return false;
            }

            $createTableQuery = $result[0]->{'Create Table'};

            // Replace table name with partition table name
            $createTableQuery = str_replace(
                "CREATE TABLE `{$baseTable}`",
                "CREATE TABLE `{$partitionTable}`",
                $createTableQuery
            );

            // Execute the CREATE TABLE statement
            DB::statement($createTableQuery);

            \Log::info("Auto-created partition table: {$partitionTable}");

            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to auto-create partition table {$partitionTable}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Set the table name to the appropriate partition and auto-create if needed
     *
     * @param string|Carbon $date
     * @return $this
     */
    public function usePartition($date)
    {
        // Auto-create partition if it doesn't exist
        $created = $this->ensurePartitionExists($date);

        if (!$created) {
            // If partition creation fails, fall back to base table
            \Log::warning("Failed to create partition for date {$date}, using base table");
            return $this;
        }

        // Set the table name to the partition
        $partitionTable = $this->getPartitionTableName($date);
        $this->setTable($partitionTable);

        return $this;
    }

    /**
     * Query across multiple partitions for date range
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return \Illuminate\Database\Query\Builder
     */
    public function queryAcrossPartitions(Carbon $startDate, Carbon $endDate)
    {
        $partitions = [];
        $current = $startDate->copy()->startOfMonth();

        while ($current <= $endDate) {
            $partitionTable = $this->getPartitionTableName($current);

            // Try to create partition if it doesn't exist
            $this->ensurePartitionExists($current);

            if (Schema::hasTable($partitionTable)) {
                $partitions[] = $partitionTable;
            }
            $current->addMonth();
        }

        if (empty($partitions)) {
            // If no partitions exist, try to use base table
            $baseTable = $this->getTable();
            if (Schema::hasTable($baseTable)) {
                return DB::table($baseTable)
                    ->whereBetween('trip_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            }

            // Return empty query if nothing exists
            return DB::table($baseTable)->whereRaw('1 = 0');
        }

        // Create UNION query across partitions
        $query = null;
        foreach ($partitions as $index => $partition) {
            $partitionQuery = DB::table($partition)
                ->whereBetween('trip_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

            if ($index === 0) {
                $query = $partitionQuery;
            } else {
                $query = $query->union($partitionQuery);
            }
        }

        return $query;
    }

    /**
     * Get all available partition tables
     *
     * @return array
     */
    public function getAllPartitionTables(): array
    {
        $baseTable = $this->getTable();
        $pattern = $baseTable . '_______'; // 6 digits for YYYYMM

        try {
            $tables = DB::select("SHOW TABLES LIKE '{$pattern}'");

            $partitionTables = [];
            foreach ($tables as $table) {
                $tableName = array_values((array) $table)[0];
                $partitionTables[] = $tableName;
            }

            return $partitionTables;
        } catch (\Exception $e) {
            \Log::error("Failed to get partition tables: " . $e->getMessage());
            return [];
        }
    }
}
