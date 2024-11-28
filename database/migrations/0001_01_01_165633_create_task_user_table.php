<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('task_user')) {
            return;
        }

        Schema::create('task_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id')->index('task_user_task_id_foreign');
            $table->unsignedBigInteger('user_id')->index('task_user_user_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_user');
    }
};
