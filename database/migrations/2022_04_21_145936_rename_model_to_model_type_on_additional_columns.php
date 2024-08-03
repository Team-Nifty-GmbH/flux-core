<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('additional_columns', function (Blueprint $table) {
            $table->renameColumn('model', 'model_type');
        });
    }

    public function down(): void
    {
        Schema::table('additional_columns', function (Blueprint $table) {
            $table->renameColumn('model_type', 'model');
        });
    }
};
