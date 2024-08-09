<?php

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        $activitiesTableName = config('activitylog.table_name');
        foreach (Relation::morphMap() as $morphAlias => $class) {
            $model = app($class);
            $tableName = $model->getTable();

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
                    ->join(DB::raw("(SELECT MIN(id) as min_id, subject_id FROM {$activitiesTableName} WHERE log_name = 'model_events' AND event = 'created' AND subject_type = '$morphAlias' GROUP BY subject_id) as oldest_activity"),
                        fn ($join) => $join->on("{$tableName}.id", '=', 'oldest_activity.subject_id'))
                    ->join($activitiesTableName, fn ($join) => $join->on("{$activitiesTableName}.id", '=', 'oldest_activity.min_id'))
                    ->where("{$activitiesTableName}.log_name", 'model_events')
                    ->where("{$activitiesTableName}.event", 'created')
                    ->update([
                        "{$tableName}.created_by" => DB::raw("CONCAT({$activitiesTableName}.causer_type, ':', {$activitiesTableName}.causer_id)"),
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
                    ->join(DB::raw("(SELECT MAX(id) as max_id, subject_id FROM {$activitiesTableName} WHERE log_name = 'model_events' AND event = 'updated' AND subject_type = '$morphAlias' GROUP BY subject_id) as newest_activity"),
                        fn ($join) => $join->on("{$tableName}.id", '=', 'newest_activity.subject_id'))
                    ->join($activitiesTableName, fn ($join) => $join->on("{$activitiesTableName}.id", '=', 'newest_activity.max_id'))
                    ->where("{$activitiesTableName}.log_name", 'model_events')
                    ->where("{$activitiesTableName}.event", 'updated')
                    ->update([
                        "{$tableName}.updated_by" => DB::raw("CONCAT({$activitiesTableName}.causer_type, ':', {$activitiesTableName}.causer_id)"),
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
                    ->join(DB::raw("(SELECT MAX(id) as max_id, subject_id FROM {$activitiesTableName} WHERE log_name = 'model_events' AND event = 'deleted' AND subject_type = '$morphAlias' GROUP BY subject_id) as newest_activity"),
                        fn ($join) => $join->on("{$tableName}.id", '=', 'newest_activity.subject_id'))
                    ->join($activitiesTableName, fn ($join) => $join->on("{$activitiesTableName}.id", '=', 'newest_activity.max_id'))
                    ->where("{$activitiesTableName}.log_name", 'model_events')
                    ->where("{$activitiesTableName}.event", 'deleted')
                    ->update([
                        "{$tableName}.deleted_by" => DB::raw("CONCAT({$activitiesTableName}.causer_type, ':', {$activitiesTableName}.causer_id)"),
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach (Relation::morphMap() as $morphAlias => $class) {
            $model = app($class);
            $tableName = $model->getTable();

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

        Schema::enableForeignKeyConstraints();
    }
};
