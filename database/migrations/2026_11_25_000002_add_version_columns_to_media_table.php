<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->foreignId('previous_version_id')
                ->nullable()
                ->after('document_type_id')
                ->constrained('media')
                ->nullOnDelete();
            $table->char('checksum', 64)->nullable()->after('size');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('previous_version_id');
            $table->dropColumn('checksum');
        });
    }
};
