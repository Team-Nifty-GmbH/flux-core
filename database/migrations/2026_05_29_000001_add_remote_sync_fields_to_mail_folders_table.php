<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('mail_folders', function (Blueprint $table): void {
            $table->string('remote_id')->nullable()->after('slug');
            $table->text('delta_link')->nullable()->after('remote_id');

            $table->index(['mail_account_id', 'remote_id'], 'mail_folders_account_remote_idx');
        });
    }

    public function down(): void
    {
        Schema::table('mail_folders', function (Blueprint $table): void {
            $table->dropIndex('mail_folders_account_remote_idx');
            $table->dropColumn(['remote_id', 'delta_link']);
        });
    }
};
