<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_types')) {
            return;
        }

        Schema::create('order_types', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('client_id')->index('order_types_client_id_foreign');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('mail_subject')->nullable();
            $table->text('mail_body')->nullable();
            $table->json('print_layouts')->nullable();
            $table->json('post_stock_print_layouts')->nullable();
            $table->json('reserve_stock_print_layouts')->nullable();
            $table->string('order_type_enum');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_hidden')->default(false);
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
        Schema::dropIfExists('order_types');
    }
};
