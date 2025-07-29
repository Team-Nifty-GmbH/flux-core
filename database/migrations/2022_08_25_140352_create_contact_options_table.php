<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('contact_options', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('address_id');
            $table->string('type');
            $table->string('label');
            $table->string('value');
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();

            $table->foreign('address_id')->references('id')->on('addresses');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_options');
    }
};
