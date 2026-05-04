<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('discounts', function (Blueprint $table): void {
            $table->foreignId('rule_id')->nullable()->after('order_column')->constrained('rules')->nullOnDelete();
            $table->boolean('is_stackable')->default(false)->after('is_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('rule_id');
            $table->dropColumn('is_stackable');
        });
    }
};
