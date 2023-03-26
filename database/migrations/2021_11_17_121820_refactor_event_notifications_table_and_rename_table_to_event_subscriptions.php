<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefactorEventNotificationsTableAndRenameTableToEventSubscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->renameEventNotificationsTableToEventSubscriptions();

        Schema::table('event_subscriptions', function (Blueprint $table) {
            $table->boolean('is_notifiable')->after('model_id')->default(false);
            $table->boolean('is_broadcast')->after('model_id')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['is_notifiable', 'is_broadcast']);
        });

        $this->renameEventSubscriptionsTableToEventNotifications();
    }

    private function renameEventNotificationsTableToEventSubscriptions()
    {
        DB::statement('RENAME TABLE event_notifications TO event_subscriptions');
    }

    private function renameEventSubscriptionsTableToEventNotifications()
    {
        DB::statement('RENAME TABLE event_subscriptions TO event_notifications');
    }
}
