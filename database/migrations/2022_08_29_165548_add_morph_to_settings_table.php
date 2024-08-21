<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropForeign('settings_user_id_foreign');
            $table->dropUnique('settings_user_id_key_unique');
            $table->dropUnique('settings_key_unique');
            $table->dropColumn('user_id');

            $table->string('model_type')->nullable()->after('key');
            $table->unsignedBigInteger('model_id')->nullable()->after('model_type');
            $table->unique(['model_id', 'model_type', 'key']);

            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique('settings_model_id_model_type_key_unique');
            $table->dropMorphs('model');
            $table->unsignedBigInteger('user_id')->nullable()->after('uuid');

            $table->unique('key');
            $table->unique(['user_id', 'key']);
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
};
