<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeProjectNameAndDisplayNameToTextOnProjectsTable extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->json('project_name')->change();
            $table->json('display_name')->nullable()->change();
        });
    }

    public function down(): void
    {
        config()->set('database.connections.mysql.strict', false);
        DB::reconnect();

        try {
            Schema::table('projects', function (Blueprint $table): void {
                $table->string('project_name')->change();
                $table->string('display_name')->nullable()->change();
            });
        } catch (Exception $e) {
            echo $e;
        } finally {
            config()->set('database.connections.mysql.strict', true);
            DB::reconnect();
        }
    }
}
