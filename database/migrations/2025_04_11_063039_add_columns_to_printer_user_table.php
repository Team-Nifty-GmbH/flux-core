<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('printer_user', function (Blueprint $table): void {
            $table->string('default_size')->nullable()->after('user_id');
            $table->boolean('is_default')->default(false)->after('default_size');
        });
    }

    public function down(): void
    {
        Schema::table('printer_user', function (Blueprint $table): void {
            $table->dropColumn(['default_size', 'is_default']);
        });
    }
};
