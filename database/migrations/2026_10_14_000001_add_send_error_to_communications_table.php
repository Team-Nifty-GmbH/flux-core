<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('communications', function (Blueprint $table): void {
            $table->text('send_error')->nullable()->after('html_body');
        });
    }

    public function down(): void
    {
        Schema::table('communications', function (Blueprint $table): void {
            $table->dropColumn('send_error');
        });
    }
};
