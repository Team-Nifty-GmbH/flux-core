<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->unsignedBigInteger('client_id')->after('uuid')->nullable();
        });

        DB::table('projects')->update([
            'client_id' => DB::table('clients')->where('is_default', true)->value('id')
                ?? DB::table('clients')->value('id'),
        ]);

        Schema::table('projects', function (Blueprint $table): void {
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            $table->unsignedBigInteger('client_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
        });
    }
};
