<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropForeign(['contact_origin_id']);
            $table->renameColumn('contact_origin_id', 'record_origin_id');
            $table->foreign('record_origin_id')
                ->references('id')
                ->on('record_origins')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropForeign(['record_origin_id']);
            $table->renameColumn('record_origin_id', 'contact_origin_id');
            $table->foreign('contact_origin_id')
                ->references('id')
                ->on('record_origins')
                ->nullOnDelete();
        });
    }
};
