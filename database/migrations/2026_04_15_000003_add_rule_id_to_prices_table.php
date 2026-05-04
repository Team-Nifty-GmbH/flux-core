<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('prices', function (Blueprint $table): void {
            $table->foreignId('rule_id')->nullable()->after('product_id')->constrained('rules')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('prices', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('rule_id');
        });
    }
};
