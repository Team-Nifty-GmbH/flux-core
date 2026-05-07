<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'overridden_fields')) {
                $table->json('overridden_fields')->nullable()->after('parent_id');
            }

            if (! Schema::hasColumn('products', 'was_parent')) {
                $table->boolean('was_parent')->default(false)->after('overridden_fields');
                $table->index('was_parent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'was_parent')) {
                $table->dropIndex(['was_parent']);
                $table->dropColumn('was_parent');
            }

            if (Schema::hasColumn('products', 'overridden_fields')) {
                $table->dropColumn('overridden_fields');
            }
        });
    }
};
