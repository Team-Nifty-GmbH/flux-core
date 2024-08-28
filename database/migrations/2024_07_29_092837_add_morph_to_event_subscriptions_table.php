<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_subscriptions', function (Blueprint $table) {
            $table->dropForeign('event_notifications_user_id_foreign');
            $table->dropIndex('event_notifications_user_id_foreign');
            $table->renameColumn('user_id', 'subscribable_id');
            $table->string('subscribable_type')->nullable()->after('subscribable_id');
        });

        DB::table('event_subscriptions')->update(['subscribable_type' => 'user']);

        Schema::table('event_subscriptions', function (Blueprint $table) {
            $table->string('subscribable_type')->nullable(false)->change();
            $table->index(['subscribable_id', 'subscribable_type']);
        });
    }

    public function down(): void
    {
        Schema::table('event_subscriptions', function (Blueprint $table) {
            $table->dropIndex(['subscribable_id', 'subscribable_type']);
            $table->renameColumn('subscribable_id', 'user_id');
            $table->dropColumn('subscribable_type');

            $table->foreign('user_id', 'event_notifications_user_id_foreign')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};
