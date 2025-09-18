<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('print_layout_snippets', function (Blueprint $table) {
            $table->longText('content')->change();
        });
    }

    public function down(): void
    {
        Schema::table('print_layout_snippets', function (Blueprint $table) {
            $table->string('content')->change();
        });
    }
};
