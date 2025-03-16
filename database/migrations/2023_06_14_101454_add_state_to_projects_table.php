<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->string('state')->after('description')->default('open');
        });

        Illuminate\Support\Facades\DB::update('UPDATE projects SET state = "done" WHERE is_done = 1');

        Schema::table('projects', function (Blueprint $table): void {
            $table->dropColumn('is_done');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->boolean('is_done')->after('description')->default(false);
        });

        Illuminate\Support\Facades\DB::update('UPDATE projects SET is_done = 1 WHERE state = "done"');

        Schema::table('projects', function (Blueprint $table): void {
            $table->dropColumn('state');
        });
    }
};
