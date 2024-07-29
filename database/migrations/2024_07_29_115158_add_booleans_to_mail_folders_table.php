<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('mail_folders', function (Blueprint $table) {
            $table->boolean('creates_ticket')->after('slug')->default(false);
            $table->boolean('creates_purchase_invoice')->after('creates_ticket')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('mail_folders', function (Blueprint $table) {
            $table->dropColumn(['creates_ticket', 'creates_purchase_invoice']);
        });
    }
};
