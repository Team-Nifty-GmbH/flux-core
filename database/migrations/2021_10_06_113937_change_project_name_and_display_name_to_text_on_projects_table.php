<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeProjectNameAndDisplayNameToTextOnProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->json('project_name')->change();
            $table->json('display_name')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        config()->set('database.connections.mysql.strict', false);
        DB::reconnect();

        try {
            Schema::table('projects', function (Blueprint $table) {
                $table->string('project_name')->change();
                $table->string('display_name')->change();
            });
        } catch (Exception $e) {
            echo $e;
        } finally {
            config()->set('database.connections.mysql.strict', true);
            DB::reconnect();
        }
    }
}
