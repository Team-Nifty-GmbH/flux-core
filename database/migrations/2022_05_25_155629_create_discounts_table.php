<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountsTable extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('discounts')) {
            Schema::create('discounts', function (Blueprint $table) {
                $table->id()
                    ->comment('An incrementing number to uniquely identify a record in this table. This also is the primary key of this table.');
                $table->char('uuid', 36)
                    ->comment('A 36 character long unique identifier string for a record within the whole application.');
                $table->unsignedBigInteger('order_position_id')->nullable()
                    ->comment('A unique identifier number for the table order_positions.');
                $table->decimal('discount', 40, 10)
                    ->comment('The number containing the actual discount.');
                $table->unsignedInteger('sort_number')->nullable()
                    ->comment('A number containing the position in the sequence of multiple discounts for one order-position.');
                $table->boolean('is_percentage')->default(true)
                    ->comment('A boolean deciding if this discount is a percentage instead of a discount in its respective currency.');
                $table->timestamp('created_at')->nullable()
                    ->comment('A timestamp reflecting the time of record-creation.');
                $table->unsignedBigInteger('created_by')->nullable()
                    ->comment('A unique identifier number for the table users of the user that created this record.');
                $table->timestamp('updated_at')->nullable()
                    ->comment('A timestamp reflecting the time of the last change for this record.');
                $table->unsignedBigInteger('updated_by')->nullable()
                    ->comment('A unique identifier number for the table users of the user that changed this record last.');
                $table->timestamp('deleted_at')->nullable()
                    ->comment('A timestamp reflecting the time of record-deletion.');
                $table->unsignedBigInteger('deleted_by')->nullable()
                    ->comment('A unique identifier number for the table users of the user that deleted this record.');

                $table->foreign('order_position_id')->references('id')->on('order_positions');
                $table->foreign('created_by')->references('id')->on('users');
                $table->foreign('updated_by')->references('id')->on('users');
                $table->foreign('deleted_by')->references('id')->on('users');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
}
