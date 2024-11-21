<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('price_lists')) {
            return;
        }

        Schema::create('price_lists', function (Blueprint $table) {
            $table->id()->comment('An incrementing number to unique identify a record in this table. This also is the primary key of this table.');
            $table->string('uuid')->comment('A 36 character long unique identifier string for a record within the whole application.');
            $table->unsignedBigInteger('parent_id')->nullable()->index('price_lists_parent_id_foreign');
            $table->text('name')->comment('A string containing a descriptive name for the current price-list.');
            $table->string('price_list_code')->unique();
            $table->string('rounding_method_enum')->default('none');
            $table->integer('rounding_precision')->nullable();
            $table->unsignedInteger('rounding_number')->nullable();
            $table->string('rounding_mode')->nullable();
            $table->boolean('is_net')->default(true)->comment('A boolean deciding if this price-list has prices only for net orders instead of gross orders.');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_purchase')->default(false);
            $table->timestamp('created_at')->nullable()->comment('A timestamp reflecting the time of record-creation.');
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable()->comment('A timestamp reflecting the time of the last change for this record.');
            $table->string('updated_by')->nullable();
            $table->softDeletes()->comment('A timestamp reflecting the time of record-deletion.');
            $table->string('deleted_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_lists');
    }
};
