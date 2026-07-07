<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            UPDATE media m
            JOIN (
                SELECT id, ROW_NUMBER() OVER (
                    PARTITION BY model_type, model_id ORDER BY name ASC, id ASC
                ) AS rn
                FROM media
            ) ranked ON ranked.id = m.id
            SET m.order_column = ranked.rn
        SQL);
    }

    public function down(): void {}
};
