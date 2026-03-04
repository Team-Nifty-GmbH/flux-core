<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('printers', function (Blueprint $table): void {
            $table->boolean('is_visible')
                ->default(false)
                ->after('is_active');
            $table->string('system_name')
                ->nullable()
                ->after('name');
            $table->string('uri')
                ->nullable()
                ->after('system_name');
        });
    }

    public function down(): void
    {
        Schema::table('printers', function (Blueprint $table): void {
            $table->dropColumn(['is_visible', 'system_name', 'uri']);
        });
    }
};
