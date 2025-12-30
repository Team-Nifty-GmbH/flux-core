<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('address_types', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('address_type_code')->nullable()
                ->comment('Used for special queries or functions, eg. order always need an address with address type \'inv\' ( invoice ).');
            $table->string('name');
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();

            $table->unique(['tenant_id', 'address_type_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('address_types');
    }
};
