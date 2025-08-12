<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('targets', function (Blueprint $table): void {
            $table->string('name')->after('parent_id');
        });
    }

    public function down(): void
    {
        Schema::table('targets', function (Blueprint $table): void {
            $table->dropColumn([
                'name',
            ]);
        });
    }
};
