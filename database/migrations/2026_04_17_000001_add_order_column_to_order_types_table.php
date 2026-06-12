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

        // Session variables are mysql-only and break sqlite installations,
        // number the rows driver-agnostically instead.
        $position = 1;

        foreach (DB::table('order_types')->orderBy('id')->pluck('id') as $id) {
            DB::table('order_types')
                ->where('id', $id)
                ->update(['order_column' => $position++]);
        }
    }

    public function down(): void
    {
        Schema::table('order_types', function (Blueprint $table): void {
            $table->dropColumn('order_column');
        });
    }
};
