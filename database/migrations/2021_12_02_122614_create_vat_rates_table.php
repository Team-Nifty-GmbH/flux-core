<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('vat_rates', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);

            $table->string('name');
            $table->decimal('rate_percentage', 40, 10);
            $table->text('footer_text')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_tax_exemption')->default(false);

            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vat_rates');
    }
};
