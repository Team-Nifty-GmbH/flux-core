<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('payment_types', function (Blueprint $table): void {
            $table->boolean('is_default')
                ->after('is_active')
                ->default(false);
        });

        DB::table('payment_types')
            ->whereNull('deleted_at')
            ->limit(1)
            ->update(['is_default' => true]);

        Schema::table('languages', function (Blueprint $table): void {
            $table->boolean('is_default')
                ->after('language_code')
                ->default(false);
        });

        DB::table('languages')
            ->whereNull('deleted_at')
            ->limit(1)
            ->update(['is_default' => true]);

        Schema::table('clients', function (Blueprint $table): void {
            $table->boolean('is_default')
                ->after('is_active')
                ->default(false);
        });

        DB::table('clients')
            ->whereNull('deleted_at')
            ->limit(1)
            ->update(['is_default' => true]);

        Schema::table('warehouses', function (Blueprint $table): void {
            $table->boolean('is_default')
                ->after('name')
                ->default(false);
        });

        DB::table('warehouses')
            ->whereNull('deleted_at')
            ->limit(1)
            ->update(['is_default' => true]);
    }

    public function down(): void
    {
        Schema::table('payment_types', function (Blueprint $table): void {
            $table->dropColumn('is_default');
        });

        Schema::table('languages', function (Blueprint $table): void {
            $table->dropColumn('is_default');
        });

        Schema::table('clients', function (Blueprint $table): void {
            $table->dropColumn('is_default');
        });

        Schema::table('warehouses', function (Blueprint $table): void {
            $table->dropColumn('is_default');
        });
    }
};
