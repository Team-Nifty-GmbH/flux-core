<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contact_options')) {
            return;
        }

        Schema::create('contact_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('address_id')->index('contact_options_address_id_foreign');
            $table->string('type');
            $table->string('label');
            $table->string('value');
            $table->timestamp('created_at')->nullable()->comment('A timestamp reflecting the time of record-creation.');
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable()->comment('A timestamp reflecting the time of the last change for this record.');
            $table->string('updated_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_options');
    }
};
