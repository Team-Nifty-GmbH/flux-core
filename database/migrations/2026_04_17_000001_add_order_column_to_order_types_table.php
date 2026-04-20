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
            ->whereRaw('0 = (@rownum := 0)')
            ->orderBy('id')
            ->update([
                'order_column' => DB::raw('@rownum := @rownum + 1'),
            ]);
    }

    public function down(): void
    {
        Schema::table('order_types', function (Blueprint $table): void {
            $table->dropColumn('order_column');
        });
    }
};
