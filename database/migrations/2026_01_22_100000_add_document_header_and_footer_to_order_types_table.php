<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('order_types', function (Blueprint $table): void {
            $table->text('document_footer')->nullable()->after('description');
            $table->text('document_header')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('order_types', function (Blueprint $table): void {
            $table->dropColumn(['document_header', 'document_footer']);
        });
    }
};
