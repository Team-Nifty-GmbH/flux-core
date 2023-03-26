<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameBooleansOnContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->renameColumn('is_sensitive_reminder', 'has_sensitive_reminder');
            $table->renameColumn('is_delivery_lock', 'has_delivery_lock');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->renameColumn('has_sensitive_reminder', 'is_sensitive_reminder');
            $table->renameColumn('has_delivery_lock', 'is_delivery_lock');
        });
    }
}
