<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_discount', function (Blueprint $table) {
            $table->foreign(['contact_id'])->references(['id'])->on('contacts')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['discount_id'])->references(['id'])->on('discounts')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('contact_discount', function (Blueprint $table) {
            $table->dropForeign('contact_discount_contact_id_foreign');
            $table->dropForeign('contact_discount_discount_id_foreign');
        });
    }
};
