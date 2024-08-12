<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('address_address_type_order', function (Blueprint $table) {
            $table->json('address')->nullable()->after('address_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('address_address_type_order', function (Blueprint $table) {
            $table->dropColumn('address');
        });
    }
};
