<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('calendars', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('id')
                ->constrained('calendars')
                ->nullOnDelete();
            $table->string('model_type')->nullable()->after('parent_id')->index();
            $table->json('custom_properties')->nullable()->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('calendars', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn([
                'parent_id',
                'model_type',
                'custom_properties',
            ]);
        });
    }
};
