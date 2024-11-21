<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_discount_group', function (Blueprint $table) {
            $table->foreign(['contact_id'])->references(['id'])->on('contacts')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['discount_group_id'])->references(['id'])->on('discount_groups')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('contact_discount_group', function (Blueprint $table) {
            $table->dropForeign('contact_discount_group_contact_id_foreign');
            $table->dropForeign('contact_discount_group_discount_group_id_foreign');
        });
    }
};
