<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

trait AutoPartitionManager
{
    /**
     * Store the original base table name
     */
    protected $baseTableName = null;

    /**
     * Get the base table name (original table name without partition suffix)
     *
     * @return string
     */
    public function getBaseTableName(): string
    {
        if ($this->baseTableName === null) {
            // Get the original table name from the model, ensure it's clean
            $originalTable = $this->table ?? 'trip_instances';

            // Remove any existing partition suffix to get the base table
            if (preg_match('/^(.+)_\d{6}$/', $originalTable, $matches)) {
                $this->baseTableName = $matches[1];
            } else {
                $this->baseTableName = $originalTable;
            }
        }

        return $this->baseTableName;
    }

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

        // Always use the base table name, not the current table name
        return $this->getBaseTableName() . '_' . $yearMonth;
    }

    /**
     * Auto-create partition table if it doesn't exist (transaction-safe)
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
            $baseTable = $this->getBaseTableName();

            // First ensure the base table exists
            if (!Schema::hasTable($baseTable)) {
                \Log::error("Base table {$baseTable} does not exist. Cannot create partition.");
                return false;
            }

            // Check if we're in a transaction - if so, use a separate connection
            $inTransaction = DB::transactionLevel() > 0;

            if ($inTransaction) {
                // Use a separate database connection for DDL operations
                $ddlConnection = DB::connection();

                // Get the CREATE TABLE statement from the base table
                $result = $ddlConnection->select("SHOW CREATE TABLE `{$baseTable}`");

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

                // Execute the CREATE TABLE statement on separate connection
                $ddlConnection->unprepared($createTableQuery);

            } else {
                // No transaction, safe to use regular connection
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
                DB::unprepared($createTableQuery);
            }

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
        // Get the partition table name
        $partitionTable = $this->getPartitionTableName($date);

        // If we're already using the correct partition, no need to do anything
        if ($this->getTable() === $partitionTable) {
            return $this;
        }

        // Auto-create partition if it doesn't exist
        $created = $this->ensurePartitionExists($date);

        if (!$created) {
            // If partition creation fails, fall back to base table
            \Log::warning("Failed to create partition for date {$date}, using base table");
            $this->setTable($this->getBaseTableName());
            return $this;
        }

        // Set the table name to the partition
        $this->setTable($partitionTable);

        return $this;
    }

    /**
     * Reset to base table
     *
     * @return $this
     */
    public function useBaseTable()
    {
        $this->setTable($this->getBaseTableName());
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
            $baseTable = $this->getBaseTableName();
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
        $baseTable = $this->getBaseTableName();
        $pattern = $baseTable . '_%______'; // Underscore + 6 digits for YYYYMM

        try {
            $tables = DB::select("SHOW TABLES LIKE '{$pattern}'");

            $partitionTables = [];
            foreach ($tables as $table) {
                $tableName = array_values((array) $table)[0];
                // Only include tables that match the exact pattern (base_YYYYMM)
                if (preg_match('/^' . preg_quote($baseTable, '/') . '_\d{6}$/', $tableName)) {
                    $partitionTables[] = $tableName;
                }
            }

            return $partitionTables;
        } catch (\Exception $e) {
            \Log::error("Failed to get partition tables: " . $e->getMessage());
            return [];
        }
    }
}
