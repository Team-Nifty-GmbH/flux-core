<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('payment_reminders', function (Blueprint $table): void {
            $table->dropForeign(['media_id']);
            $table->dropColumn('media_id');
        });
    }

    public function down(): void
    {
        Schema::table('payment_reminders', function (Blueprint $table): void {
            $table->unsignedBigInteger('media_id')->nullable()->after('uuid');
            $table->foreign('media_id')->references('id')->on('media')->nullOnDelete();
        });
    }
};
