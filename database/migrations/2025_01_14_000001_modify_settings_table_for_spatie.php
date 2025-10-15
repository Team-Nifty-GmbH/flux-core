<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->dropColumn([
                'uuid',
                'key',
                'model_type',
                'model_id',
                'settings',
            ]);
        });

        Schema::table('settings', function (Blueprint $table): void {
            $table->string('group')->nullable()->after('id');
            $table->string('name')->nullable()->after('group');
            $table->boolean('locked')->default(false)->after('name');
            $table->json('payload')->nullable()->after('locked');

            $table->unique(['group', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->dropColumn([
                'group',
                'name',
                'locked',
                'payload',
            ]);
        });

        Schema::table('settings', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('key')->unique()->index();
            $table->json('settings')->nullable();
        });
    }
};
