<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('printers', function (Blueprint $table): void {
            $table->string('alias')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('printers', function (Blueprint $table): void {
            $table->dropColumn('alias');
        });
    }
};
