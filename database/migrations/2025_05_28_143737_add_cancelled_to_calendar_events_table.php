<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_events', function (Blueprint $table): void {
            $table->json('cancelled')->nullable()->after('recurrences');
            $table->timestamp('cancelled_at')->nullable()->after('updated_by');
            $table->string('cancelled_by')->nullable()->after('cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table): void {
            $table->dropColumn([
                'cancelled',
                'cancelled_at',
                'cancelled_by',
            ]);
        });
    }
};
