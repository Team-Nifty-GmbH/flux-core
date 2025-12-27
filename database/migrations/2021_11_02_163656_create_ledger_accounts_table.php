<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ledger_accounts')) {
            return;
        }

        Schema::create('ledger_accounts', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->cascadeOnDelete();
            $table->string('number');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('ledger_account_type_enum');
            $table->boolean('is_automatic')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();

            $table->unique(['number', 'ledger_account_type_enum', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_accounts');
    }
};
