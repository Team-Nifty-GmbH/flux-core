<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('prices')) {
            return;
        }

        Schema::create('prices', function (Blueprint $table) {
            $table->id()->comment('An incrementing number to uniquely identify a record in this table. This also is the primary key of this table.');
            $table->string('uuid')->comment('A 36 character long unique identifier string for a record within the whole application.');
            $table->unsignedBigInteger('product_id')->index('prices_product_id_foreign')->comment('A unique identifier number for the table products.');
            $table->unsignedBigInteger('price_list_id')->index('prices_price_list_id_foreign')->comment('A unique identifier number for the table price_lists.');
            $table->decimal('price', 40, 10)->comment('The actual price as number for this database entry.');
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
        Schema::dropIfExists('prices');
    }
};
