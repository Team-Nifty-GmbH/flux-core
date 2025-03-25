<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('tokens', function (Blueprint $table): void {
            $table->string('name')->nullable()->after('id');
            $table->text('description')->nullable()->after('name');
            $table->json('abilities')->nullable()->after('url');
        });
    }

    public function down(): void
    {
        Schema::table('tokens', function (Blueprint $table): void {
            $table->dropColumn(['name', 'description', 'abilities']);
        });
    }
};
