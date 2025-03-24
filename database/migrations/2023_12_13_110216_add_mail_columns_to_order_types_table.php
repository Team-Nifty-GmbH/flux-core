<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('order_types', function (Blueprint $table): void {
            $table->string('mail_subject')->nullable()->after('description');
            $table->text('mail_body')->nullable()->after('mail_subject');
        });
    }

    public function down(): void
    {
        Schema::table('order_types', function (Blueprint $table): void {
            $table->dropColumn('mail_subject');
            $table->dropColumn('mail_body');
        });
    }
};
