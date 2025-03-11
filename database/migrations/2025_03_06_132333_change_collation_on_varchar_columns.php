<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->string('name')->collation('utf8mb4_unicode_ci')->change();
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->string('name')->collation('utf8mb4_unicode_ci')->change();
        });

        Schema::table('additional_columns', function (Blueprint $table): void {
            $table->string('name')->collation('utf8mb4_unicode_ci')->change();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->string('name')->collation('utf8mb4_bin')->change();
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->string('name')->collation('utf8mb4_bin')->change();
        });

        Schema::table('additional_columns', function (Blueprint $table): void {
            $table->string('name')->collation('utf8mb4_bin')->change();
        });
    }
};
