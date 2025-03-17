<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveRequestHashColumnFromPrintData extends Migration
{
    public function up(): void
    {
        Schema::table('print_data', function (Blueprint $table): void {
            $table->dropColumn('request_hash');
        });
    }

    public function down(): void
    {
        Schema::table('print_data', function (Blueprint $table): void {
            $table->string('request_hash')->unique()->index()->after('template_name');
        });
    }
}
