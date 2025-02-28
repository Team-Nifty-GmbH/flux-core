<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('datatable_user_settings', function (Blueprint $table) {
            $table->string('cache_key')->after('name')->index();

        });

        DB::table('datatable_user_settings')->update(['cache_key' => DB::raw('component')]);
    }

    public function down(): void
    {
        Schema::table('datatable_user_settings', function (Blueprint $table) {
            $table->dropColumn('cache_key');
        });
    }
};
