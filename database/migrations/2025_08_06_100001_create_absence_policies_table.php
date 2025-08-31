<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('absence_policies', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);

            $table->string('name');
            $table->integer('max_consecutive_days')->nullable();
            $table->integer('min_notice_days')->nullable();
            $table->integer('documentation_after_days')->nullable();

            $table->boolean('can_select_substitute')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_documentation')->default(false);
            $table->boolean('requires_proof')->default(false);
            $table->boolean('requires_reason')->default(false);
            $table->boolean('requires_substitute')->default(false);

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
        Schema::dropIfExists('absence_policies');
    }
};
