<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameBooleansOnContactsTable extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table): void {
            $table->renameColumn('is_sensitive_reminder', 'has_sensitive_reminder');
            $table->renameColumn('is_delivery_lock', 'has_delivery_lock');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table): void {
            $table->renameColumn('has_sensitive_reminder', 'is_sensitive_reminder');
            $table->renameColumn('has_delivery_lock', 'is_delivery_lock');
        });
    }
}
