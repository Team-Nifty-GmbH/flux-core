<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefactorEventNotificationsTableAndRenameTableToEventSubscriptions extends Migration
{
    public function up(): void
    {
        $this->renameEventNotificationsTableToEventSubscriptions();

        Schema::table('event_subscriptions', function (Blueprint $table): void {
            $table->boolean('is_notifiable')->after('model_id')->default(false);
            $table->boolean('is_broadcast')->after('model_id')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('event_subscriptions', function (Blueprint $table): void {
            $table->dropColumn(['is_notifiable', 'is_broadcast']);
        });

        $this->renameEventSubscriptionsTableToEventNotifications();
    }

    private function renameEventNotificationsTableToEventSubscriptions(): void
    {
        DB::statement('RENAME TABLE event_notifications TO event_subscriptions');
    }

    private function renameEventSubscriptionsTableToEventNotifications(): void
    {
        DB::statement('RENAME TABLE event_subscriptions TO event_notifications');
    }
}
