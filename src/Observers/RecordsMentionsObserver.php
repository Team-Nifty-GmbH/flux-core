<?php

namespace FluxErp\Observers;

use FluxErp\Contracts\MentionsContent;
use FluxErp\Contracts\ProvidesMentionNotification;
use FluxErp\Enums\MentionTypeEnum;
use FluxErp\Models\Mention;
use FluxErp\Models\User;
use FluxErp\Notifications\MentionNotification;
use FluxErp\Support\Mentions\MentionSync;
use Illuminate\Database\Eloquent\Model;

class RecordsMentionsObserver
{
    public function __construct(private readonly MentionSync $sync) {}

    public function saved(Model&MentionsContent $source): void
    {
        $result = $this->sync->sync($source);

        foreach ($result->added as $row) {
            $this->dispatchForAddedRow($source, $row);
        }
    }

    public function deleted(Model&MentionsContent $source): void
    {
        Mention::query()
            ->where('mention_source_type', $source->getMorphClass())
            ->where('mention_source_id', $source->getKey())
            ->delete();
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function dispatchForAddedRow(Model $source, array $row): void
    {
        $type = $row['mention_type_enum'];

        if ($type === MentionTypeEnum::User && $row['user_id']) {
            $notification = $source instanceof ProvidesMentionNotification
                ? $source->mentionNotification()
                : new MentionNotification($source);

            User::query()->whereKey($row['user_id'])->first()?->notify($notification);

            return;
        }

        if ($type !== MentionTypeEnum::Record || ! $row['mention_target_type']) {
            return;
        }

        $cls = function_exists('morphed_model') ? morphed_model($row['mention_target_type']) : null;
        $target = $cls ? $cls::query()->whereKey($row['mention_target_id'])->first() : null;
        if (! $target) {
            return;
        }

        $creator = auth()->user();
        $canMention = $creator
            && method_exists($creator, 'can')
            && $creator->can('mention', $target);

        if (! $canMention) {
            return;
        }

        if (method_exists($target, 'subscribeNotificationChannel')) {
            $target->subscribeNotificationChannel(
                channel: method_exists($source, 'broadcastChannel')
                    ? $source->broadcastChannel()
                    : $source->getMorphClass() . '.' . $source->getKey(),
                event: 'eloquent.created: ' . $source::class,
            );
        }
    }
}
