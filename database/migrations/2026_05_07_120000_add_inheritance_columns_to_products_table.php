<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->json('overridden_fields')->nullable()->after('warning_stock_amount');
            $table->boolean('was_parent')->default(false)->after('is_shipping_free')->index();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex(['was_parent']);
            $table->dropColumn(['was_parent', 'overridden_fields']);
        });
    }
};
