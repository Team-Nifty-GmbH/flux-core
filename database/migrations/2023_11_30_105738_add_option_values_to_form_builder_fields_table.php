<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('form_builder_fields', function (Blueprint $table) {
            $table->json('option_values')->nullable()->after('options');
        });
    }

    public function down(): void
    {
        Schema::table('form_builder_fields', function (Blueprint $table) {
            $table->dropColumn('option_values');
        });
    }
};
