<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('discount_discount_group', function (Blueprint $table) {
            $table->foreign(['discount_group_id'])->references(['id'])->on('discount_groups')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['discount_id'])->references(['id'])->on('discounts')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('discount_discount_group', function (Blueprint $table) {
            $table->dropForeign('discount_discount_group_discount_group_id_foreign');
            $table->dropForeign('discount_discount_group_discount_id_foreign');
        });
    }
};
