<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->unsignedInteger('cups_job_id')->nullable()->after('is_completed');
            $table->string('status', 50)->nullable()->after('cups_job_id');
            $table->text('error_message')->nullable()->after('status');
            $table->timestamp('printed_at')->nullable()->after('error_message');
        });
    }

    public function down(): void
    {
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->dropColumn(['cups_job_id', 'status', 'error_message', 'printed_at']);
        });
    }
};
