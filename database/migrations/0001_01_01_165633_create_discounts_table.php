<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('discounts')) {
            return;
        }

        Schema::create('discounts', function (Blueprint $table) {
            $table->id()->comment('An incrementing number to uniquely identify a record in this table. This also is the primary key of this table.');
            $table->char('uuid', 36)->comment('A 36 character long unique identifier string for a record within the whole application.');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->decimal('discount', 40, 10)->comment('The number containing the actual discount.');
            $table->timestamp('from')->nullable();
            $table->timestamp('till')->nullable();
            $table->unsignedInteger('sort_number')->nullable()->comment('A number containing the position in the sequence of multiple discounts for one order-position.');
            $table->boolean('is_percentage')->default(true)->comment('A boolean deciding if this discount is a percentage instead of a discount in its respective currency.');
            $table->timestamp('created_at')->nullable()->comment('A timestamp reflecting the time of record-creation.');
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable()->comment('A timestamp reflecting the time of the last change for this record.');
            $table->string('updated_by')->nullable();
            $table->softDeletes()->comment('A timestamp reflecting the time of record-deletion.');
            $table->string('deleted_by')->nullable();

            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
