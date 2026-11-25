<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('document_types', function (Blueprint $table): void {
            $table->boolean('is_payroll_record')->default(false)->after('is_owner_uploadable');
        });
    }

    public function down(): void
    {
        Schema::table('document_types', function (Blueprint $table): void {
            $table->dropColumn('is_payroll_record');
        });
    }
};
