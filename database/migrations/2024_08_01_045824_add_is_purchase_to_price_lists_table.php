<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_lists', function (Blueprint $table) {
            $table->boolean('is_purchase')->default(false)->after('is_default');
        });

        DB::table('price_lists')->insert([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'name' => __('Purchase Price'),
            'price_list_code' => 'purchase',
            'is_purchase' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('price_lists', function (Blueprint $table) {
            $table->dropColumn('is_purchase');
        });
    }
};
