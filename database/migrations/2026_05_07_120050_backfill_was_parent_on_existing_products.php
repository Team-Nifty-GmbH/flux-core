<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'was_parent')) {
            return;
        }

        // One-shot backfill. On installs with 100k+ products this single UPDATE
        // holds row and gap locks for its full duration; large operators may prefer
        // to run the equivalent SQL manually in batches before deploying this migration.
        DB::statement('
            UPDATE products
            SET was_parent = 1
            WHERE id IN (
                SELECT DISTINCT parent_id
                FROM (SELECT parent_id FROM products WHERE parent_id IS NOT NULL) AS p
            )
        ');
    }

    public function down(): void
    {
        // No-op: schema down() in the prior migration drops the column entirely.
    }
};
