<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    private string $activitiesTableName;

    private array $tableNames = [
        'addresses' => 'address',
        'address_types' => 'address_type',
        'categories' => 'category',
        'clients' => 'client',
        'comments' => 'comment',
        'contacts' => 'contact',
        'contact_bank_connections' => 'contact_bank_connection',
        'contact_options' => 'contact_option',
        'countries' => 'country',
        'country_regions' => 'country_region',
        'currencies' => 'currency',
        'discounts' => 'discount',
        'interface_users' => 'interface_user',
        'languages' => 'language',
        'locks' => 'lock',
        'orders' => 'order',
        'order_positions' => 'order_position',
        'order_types' => 'order_type',
        'payment_types' => 'payment_type',
        'prices' => 'price',
        'price_lists' => 'price_list',
        'products' => 'product',
        'product_options' => 'product_option',
        'product_option_groups' => 'product_option_group',
        'product_properties' => 'product_property',
        'projects' => 'project',
        'sepa_mandates' => 'sepa_mandate',
        'serial_numbers' => 'serial_number',
        'serial_number_ranges' => 'serial_number_range',
        'snapshots' => 'snapshot',
        'stock_postings' => 'stock_posting',
        'tasks' => 'task',
        'tickets' => 'ticket',
        'ticket_types' => 'ticket_type',
        'units' => 'unit',
        'users' => 'user',
        'vat_rates' => 'vat_rate',
        'warehouses' => 'warehouse',
    ];

    public function __construct()
    {
        $this->activitiesTableName = config('activitylog.table_name');
    }

    public function up(): void
    {
        foreach ($this->tableNames as $tableName => $morphAlias) {
            $this->migrateUp($tableName, $morphAlias);
        }
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ($this->tableNames as $tableName => $morphAlias) {
            $this->migrateDown($tableName);
        }

        Schema::enableForeignKeyConstraints();
    }

    private function migrateUp(string $tableName, string $morphAlias): void
    {
        if (Schema::hasColumn($tableName, 'created_by')) {
            try {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['created_by']);
                });
            } catch (QueryException) {
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->string('created_by')->nullable()->change();
            });

            DB::table($tableName)
                ->join(DB::raw("(SELECT MIN(id) as min_id, subject_id FROM {$this->activitiesTableName} WHERE log_name = 'model_events' AND event = 'created' AND subject_type = '$morphAlias' GROUP BY subject_id) as oldest_activity"),
                    fn ($join) => $join->on("{$tableName}.id", '=', 'oldest_activity.subject_id'))
                ->join($this->activitiesTableName, fn ($join) => $join->on("{$this->activitiesTableName}.id", '=', 'oldest_activity.min_id'))
                ->where("{$this->activitiesTableName}.log_name", 'model_events')
                ->where("{$this->activitiesTableName}.event", 'created')
                ->update([
                    "{$tableName}.created_by" => DB::raw("CONCAT({$this->activitiesTableName}.causer_type, ':', {$this->activitiesTableName}.causer_id)"),
                ]);
        }

        if (Schema::hasColumn($tableName, 'updated_by')) {
            try {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['updated_by']);
                });
            } catch (QueryException) {
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->string('updated_by')->nullable()->change();
            });

            DB::table($tableName)
                ->join(DB::raw("(SELECT MAX(id) as max_id, subject_id FROM {$this->activitiesTableName} WHERE log_name = 'model_events' AND event = 'updated' AND subject_type = '$morphAlias' GROUP BY subject_id) as newest_activity"),
                    fn ($join) => $join->on("{$tableName}.id", '=', 'newest_activity.subject_id'))
                ->join($this->activitiesTableName, fn ($join) => $join->on("{$this->activitiesTableName}.id", '=', 'newest_activity.max_id'))
                ->where("{$this->activitiesTableName}.log_name", 'model_events')
                ->where("{$this->activitiesTableName}.event", 'updated')
                ->update([
                    "{$tableName}.updated_by" => DB::raw("CONCAT({$this->activitiesTableName}.causer_type, ':', {$this->activitiesTableName}.causer_id)"),
                ]);
        }

        if (Schema::hasColumn($tableName, 'deleted_by')) {
            try {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['deleted_by']);
                });
            } catch (QueryException) {
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->string('deleted_by')->nullable()->change();
            });

            DB::table($tableName)
                ->join(DB::raw("(SELECT MAX(id) as max_id, subject_id FROM {$this->activitiesTableName} WHERE log_name = 'model_events' AND event = 'deleted' AND subject_type = '$morphAlias' GROUP BY subject_id) as newest_activity"),
                    fn ($join) => $join->on("{$tableName}.id", '=', 'newest_activity.subject_id'))
                ->join($this->activitiesTableName, fn ($join) => $join->on("{$this->activitiesTableName}.id", '=', 'newest_activity.max_id'))
                ->where("{$this->activitiesTableName}.log_name", 'model_events')
                ->where("{$this->activitiesTableName}.event", 'deleted')
                ->update([
                    "{$tableName}.deleted_by" => DB::raw("CONCAT({$this->activitiesTableName}.causer_type, ':', {$this->activitiesTableName}.causer_id)"),
                ]);
        }
    }

    private function migrateDown(string $tableName): void
    {
        if (Schema::hasColumn($tableName, 'created_by')) {
            DB::table($tableName)
                ->where(DB::raw("SUBSTRING_INDEX(created_by, ':', -1)"), 'REGEXP', '^[0-9]+$')
                ->update([
                    'created_by' => DB::raw("SUBSTRING_INDEX(created_by, ':', -1)"),
                ]);

            DB::table($tableName)
                ->where(DB::raw("SUBSTRING_INDEX(created_by, ':', -1)"), 'NOT REGEXP', '^[0-9]+$')
                ->update([
                    'created_by' => null,
                ]);

            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('created_by')->nullable()->change();
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (Schema::hasColumn($tableName, 'updated_by')) {
            DB::table($tableName)
                ->where(DB::raw("SUBSTRING_INDEX(updated_by, ':', -1)"), 'REGEXP', '^[0-9]+$')
                ->update([
                    'updated_by' => DB::raw("SUBSTRING_INDEX(updated_by, ':', -1)"),
                ]);

            DB::table($tableName)
                ->where(DB::raw("SUBSTRING_INDEX(updated_by, ':', -1)"), 'NOT REGEXP', '^[0-9]+$')
                ->update([
                    'updated_by' => null,
                ]);

            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('updated_by')->nullable()->change();
                $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (Schema::hasColumn($tableName, 'deleted_by')) {
            DB::table($tableName)
                ->where(DB::raw("SUBSTRING_INDEX(deleted_by, ':', -1)"), 'REGEXP', '^[0-9]+$')
                ->update([
                    'deleted_by' => DB::raw("SUBSTRING_INDEX(deleted_by, ':', -1)"),
                ]);

            DB::table($tableName)
                ->where(DB::raw("SUBSTRING_INDEX(deleted_by, ':', -1)"), 'NOT REGEXP', '^[0-9]+$')
                ->update([
                    'deleted_by' => null,
                ]);

            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('deleted_by')->nullable()->change();
                $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }
};
