<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table): void {
            $table->id();
            $table->uuid()->unique();

            $table->morphs('authenticatable');
            $table->string('device_id')->index();
            $table->string('device_name')->nullable();
            $table->string('device_model')->nullable();
            $table->string('device_manufacturer')->nullable();
            $table->string('device_os_version')->nullable();
            $table->string('token');
            $table->string('platform');

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->softDeletes();
            $table->string('deleted_by')->nullable();

            $table->unique(
                [
                    'device_id',
                    'authenticatable_type',
                    'authenticatable_id',
                ],
                'device_tokens_unique_auth'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
