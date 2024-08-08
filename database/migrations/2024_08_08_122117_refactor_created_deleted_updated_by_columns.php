<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        // loop over all tables in Schema
        // check if they have created_by, updated_by, deleted_by columns
        // drop the foreign key constraints
        // make the columns varchar

        foreach (Schema::getTables() as $table) {
            if (Schema::hasColumn($table['name'], 'created_by')) {
                try {
                    Schema::table($table['name'], function (Blueprint $table) {
                        $table->dropForeign(['created_by']);
                    });
                } catch (QueryException) {
                }

                Schema::table($table['name'], function (Blueprint $table) {
                    $table->string('created_by')->nullable()->change();
                });
            }
            if (Schema::hasColumn($table['name'], 'updated_by')) {
                try {
                    Schema::table($table['name'], function (Blueprint $table) {
                        $table->dropForeign(['updated_by']);
                    });
                } catch (QueryException) {
                }

                Schema::table($table['name'], function (Blueprint $table) {
                    $table->string('updated_by')->nullable()->change();
                });
            }
            if (Schema::hasColumn($table['name'], 'deleted_by')) {
                try {
                    Schema::table($table['name'], function (Blueprint $table) {
                        $table->dropForeign(['deleted_by']);
                    });
                } catch (QueryException) {
                }

                Schema::table($table['name'], function (Blueprint $table) {
                    $table->string('deleted_by')->nullable()->change();
                });
            }

        }
    }

    public function down(): void
    {
        //
    }
};
