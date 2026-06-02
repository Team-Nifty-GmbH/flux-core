<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration
{
    public function up(): void
    {
        // INNER JOIN derives the parent id set; MySQL forbids referencing
        // `products` in an `UPDATE ... WHERE id IN (SELECT … FROM products …)`,
        // so the JOIN form is the portable shape.
        DB::statement('
            UPDATE products
            INNER JOIN (
                SELECT DISTINCT parent_id AS id
                FROM products
                WHERE parent_id IS NOT NULL
            ) AS parents ON products.id = parents.id
            SET products.was_parent = 1
        ');
    }

    public function down(): void {}
};
