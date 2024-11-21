<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('communicatable', function (Blueprint $table) {
            $table->foreign(['communication_id'])->references(['id'])->on('communications')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('communicatable', function (Blueprint $table) {
            $table->dropForeign('communicatable_communication_id_foreign');
        });
    }
};
