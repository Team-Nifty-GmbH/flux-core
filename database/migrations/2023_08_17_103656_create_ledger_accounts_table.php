<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_accounts', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('client_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('number');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('ledger_account_type_enum');
            $table->boolean('is_automatic')->default(false);
            $table->timestamps();

            $table->unique(['number', 'ledger_account_type_enum', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_accounts');
    }
};
