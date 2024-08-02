<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('communications', function (Blueprint $table) {
            $table->dateTime('started_at')->nullable()->after('date');
            $table->dateTime('ended_at')->nullable()->after('started_at');
            $table->unsignedBigInteger('total_time_ms')->default(0)->after('ended_at');
        });
    }

    public function down(): void
    {
        Schema::table('communications', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'ended_at', 'total_time_ms']);
        });
    }
};
