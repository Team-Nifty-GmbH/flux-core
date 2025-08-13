<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('event_subscriptions', function (Blueprint $table): void {
            $table->string('channel')->nullable()->index()->after('id');
        });

        $this->migrateEventSubscriptions();

        Schema::table('event_subscriptions', function (Blueprint $table): void {
            $table->string('channel')->nullable(false)->change();

            $table->dropColumn(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::table('event_subscriptions', function (Blueprint $table): void {
            $table->string('model_type')->nullable()->after('subscribable_type');
            $table->unsignedBigInteger('model_id')->nullable()->after('model_type');

            $table->index(['model_type', 'model_id']);
        });

        $this->rollbackEventSubscriptions();

        Schema::table('event_subscriptions', function (Blueprint $table): void {
            $table->string('model_type')->nullable(false)->change();

            $table->dropColumn('channel');
        });
    }

    private function migrateEventSubscriptions(): void
    {
        DB::table('event_subscriptions')
            ->update([
                'channel' => DB::raw("CONCAT(model_type, '.', model_id)"),
            ]);
    }

    private function rollbackEventSubscriptions(): void
    {
        DB::table('event_subscriptions')
            ->update([
                'event' => 'eloquent.created: FluxErp\\Models\\Comment',
                'model_type' => DB::raw("SUBSTRING_INDEX(channel, '.', 1)"),
                'model_id' => DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(channel, '.', 2), '.', -1)"),
            ]);

        DB::table('event_subscriptions')
            ->whereNull('model_type')
            ->delete();
    }
};
