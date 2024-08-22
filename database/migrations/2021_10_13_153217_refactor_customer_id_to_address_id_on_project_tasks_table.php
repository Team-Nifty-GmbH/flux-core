<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefactorCustomerIdToAddressIdOnProjectTasksTable extends Migration
{
    public function up(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('address_id')->after('category_id');
        });

        $this->migrateAddressId();

        Schema::table('project_tasks', function (Blueprint $table) {
            $table->foreign('address_id')->references('id')->on('addresses');
            $table->dropColumn('customer_id');
        });
    }

    public function down(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->after('category_id');
        });

        $this->rollbackAddressId();

        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropForeign('project_tasks_address_id_foreign');
            $table->dropColumn('address_id');
        });
    }

    private function migrateAddressId()
    {
        DB::statement('UPDATE project_tasks SET address_id = customer_id');
    }

    private function rollbackAddressId()
    {
        DB::statement('UPDATE project_tasks SET customer_id = address_id');
    }
}
