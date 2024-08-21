<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('payment_reminders', function (Blueprint $table) {
            $table->string('created_by')->after('created_at')->nullable();
            $table->string('updated_by')->after('updated_at')->nullable();
            $table->string('deleted_by')->after('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payment_reminders', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });
    }
};