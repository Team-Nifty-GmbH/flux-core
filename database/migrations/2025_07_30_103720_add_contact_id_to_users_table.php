<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'contact_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('contact_id')
                ->nullable()
                ->after('uuid')
                ->constrained('contacts')
                ->nullOnDelete();
        });
    }

    public function down(): void {}
};
