<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('order_types', function (Blueprint $table): void {
            $table->unsignedInteger('order_column')->nullable()->after('order_type_enum');
        });

        DB::table('order_types')
            ->orderBy('id')
            ->pluck('id')
            ->each(function ($id, $index): void {
                DB::table('order_types')
                    ->where('id', $id)
                    ->update(['order_column' => $index + 1]);
            });
    }

    public function down(): void
    {
        Schema::table('order_types', function (Blueprint $table): void {
            $table->dropColumn('order_column');
        });
    }
};
