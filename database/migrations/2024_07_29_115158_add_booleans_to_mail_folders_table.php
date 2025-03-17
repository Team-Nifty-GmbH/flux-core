<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('mail_folders', function (Blueprint $table): void {
            $table->boolean('can_create_ticket')->after('slug')->default(false);
            $table->boolean('can_create_purchase_invoice')->after('can_create_ticket')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('mail_folders', function (Blueprint $table): void {
            $table->dropColumn(['can_create_ticket', 'can_create_purchase_invoice']);
        });
    }
};
