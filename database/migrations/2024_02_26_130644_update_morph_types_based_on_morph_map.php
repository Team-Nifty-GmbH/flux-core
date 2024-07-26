<?php

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

return new class() extends Migration
{
    private array $morphMap;

    public function __construct()
    {
        $this->morphMap = Relation::morphMap();
    }

    public function up(): void
    {
        $this->migrateMorphTypes();
    }

    public function down(): void
    {
        $this->migrateMorphTypes(true);
    }

    private function migrateMorphTypes(bool $rollback = false): void
    {
        if (! $rollback) {
            $this->morphMap = array_flip($this->morphMap);
        }

        $activities = DB::table('activity_log')
            ->whereNotNull('causer_type')
            ->orWhereNotNull('subject_type')
            ->distinct()
            ->get(['causer_type', 'subject_type']);
        $this->migrate('activity_log', ['causer_type', 'subject_type'], $activities);

        $additionalColumns = DB::table('additional_columns')
            ->distinct()
            ->get(['model_type']);
        $this->migrate('additional_columns', ['model_type'], $additionalColumns);

        $calendarGroups = DB::table('calendar_groups')
            ->distinct()
            ->get(['calendarable_type']);
        $this->migrate('calendar_groups', ['calendarable_type'], $calendarGroups);

        $calendarables = DB::table('calendarables')
            ->distinct()
            ->get(['calendarable_type']);
        $this->migrate('calendarables', ['calendarable_type'], $calendarables);

        $categories = DB::table('categories')
            ->distinct()
            ->get(['model_type']);
        $this->migrate('categories', ['model_type'], $categories);

        $categorizables = DB::table('categorizables')
            ->distinct()
            ->get(['categorizable_type']);
        $this->migrate('categorizables', ['categorizable_type'], $categorizables);

        $comments = DB::table('comments')
            ->distinct()
            ->get(['model_type']);
        $this->migrate('comments', ['model_type'], $comments);

        $communicatable = DB::table('communicatable')
            ->distinct()
            ->get(['communicatable_type']);
        $this->migrate('communicatable', ['communicatable_type'], $communicatable);

        $datatableUserSettings = DB::table('datatable_user_settings')
            ->distinct()
            ->get(['authenticatable_type']);
        $this->migrate('datatable_user_settings', ['authenticatable_type'], $datatableUserSettings);

        $discounts = DB::table('discounts')
            ->distinct()
            ->get(['model_type']);
        $this->migrate('discounts', ['model_type'], $discounts);

        $eventSubscriptions = DB::table('event_subscriptions')
            ->distinct()
            ->get(['model_type']);
        $this->migrate('event_subscriptions', ['model_type'], $eventSubscriptions);

        $favorites = DB::table('favorites')
            ->distinct()
            ->get(['authenticatable_type']);
        $this->migrate('favorites', ['authenticatable_type'], $favorites);

        $formBuilderForms = DB::table('form_builder_forms')
            ->whereNotNull('model_type')
            ->distinct()
            ->get(['model_type']);
        $this->migrate('form_builder_forms', ['model_type'], $formBuilderForms);

        $inviteables = DB::table('inviteables')
            ->distinct()
            ->get(['inviteable_type']);
        $this->migrate('inviteables', ['inviteable_type'], $inviteables);

        $media = DB::table('media')
            ->distinct()
            ->get(['model_type']);
        $this->migrate('media', ['model_type'], $media);

        $meta = DB::table('meta')
            ->distinct()
            ->get(['model_type']);
        $this->migrate('meta', ['model_type'], $meta);

        $modelHasPermissions = DB::table('model_has_permissions')
            ->distinct()
            ->get(['model_type']);
        $this->migrate('model_has_permissions', ['model_type'], $modelHasPermissions);

        $modelHasRoles = DB::table('model_has_roles')
            ->distinct()
            ->get(['model_type']);
        $this->migrate('model_has_roles', ['model_type'], $modelHasRoles);

        $modelRelated = DB::table('model_related')
            ->distinct()
            ->get(['model_type', 'related_type']);
        $this->migrate('model_related', ['model_type', 'related_type'], $modelRelated);

        $notificationSettings = DB::table('notification_settings')
            ->whereNotNull('notifiable_type')
            ->distinct()
            ->get(['notifiable_type']);
        $this->migrate('notification_settings', ['notifiable_type'], $notificationSettings);

        $notifications = DB::table('notifications')
            ->distinct()
            ->get(['notifiable_type']);
        $this->migrate('notifications', ['notifiable_type'], $notifications);

        $personalAccessTokens = DB::table('personal_access_tokens')
            ->distinct()
            ->get(['tokenable_type']);
        $this->migrate('personal_access_tokens', ['tokenable_type'], $personalAccessTokens);

        $pushSubscriptions = DB::table('push_subscriptions')
            ->distinct()
            ->get(['subscribable_type']);
        $this->migrate('push_subscriptions', ['subscribable_type'], $pushSubscriptions);

        $serialNumberRanges = DB::table('serial_number_ranges')
            ->distinct()
            ->get(['model_type']);
        $this->migrate('serial_number_ranges', ['model_type'], $serialNumberRanges);

        $settings = DB::table('settings')
            ->whereNotNull('model_type')
            ->distinct()
            ->get(['model_type']);
        $this->migrate('settings', ['model_type'], $settings);

        $snapshots = DB::table('snapshots')
            ->distinct()
            ->get(['model_type']);
        $this->migrate('snapshots', ['model_type'], $snapshots);

        $taggables = DB::table('taggables')
            ->distinct()
            ->get(['taggable_type']);
        $this->migrate('taggables', ['taggable_type'], $taggables);

        $tags = DB::table('tags')
            ->whereNotNull('type')
            ->distinct()
            ->get(['type']);
        $this->migrate('tags', ['type'], $tags);

        $ticketTypes = DB::table('ticket_types')
            ->whereNotNull('model_type')
            ->distinct()
            ->get(['model_type']);
        $this->migrate('ticket_types', ['model_type'], $ticketTypes);

        $tickets = DB::table('tickets')
            ->distinct()
            ->get(['authenticatable_type', 'model_type']);
        $this->migrate('tickets', ['authenticatable_type', 'model_type'], $tickets);

        $widgets = DB::table('widgets')
            ->distinct()
            ->get(['widgetable_type']);
        $this->migrate('widgets', ['widgetable_type'], $widgets);

        $workTimes = DB::table('work_times')
            ->whereNotNull('trackable_type')
            ->distinct()
            ->get(['trackable_type']);
        $this->migrate('work_times', ['trackable_type'], $workTimes);
    }

    private function migrate(string $table, array $columns, Collection $items): void
    {
        $items->each(function ($item) use ($table, $columns) {
            foreach ($columns as $column) {
                if (is_null($item->{$column})) {
                    continue;
                }

                DB::table($table)
                    ->where($column, $item->{$column})
                    ->update([$column => $this->morphMap[$item->{$column}] ?? $item->{$column}]);
            }
        });
    }
};
