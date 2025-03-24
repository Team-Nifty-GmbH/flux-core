<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('serial_number_ranges', function (Blueprint $table): void {
            $table->renameColumn('affix', 'suffix');
        });
    }

    public function down(): void
    {
        Schema::table('serial_number_ranges', function (Blueprint $table): void {
            $table->renameColumn('suffix', 'affix');
        });
    }
};
