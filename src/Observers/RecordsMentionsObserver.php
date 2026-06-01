<?php

namespace FluxErp\Observers;

use FluxErp\Contracts\IsSubscribable;
use FluxErp\Contracts\MentionsContent;
use FluxErp\Enums\MentionTypeEnum;
use FluxErp\Models\Mention;
use FluxErp\Models\User;
use FluxErp\Notifications\MentionNotification;
use FluxErp\Services\Mentions\MentionSync;
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
    private function dispatchForAddedRow(Model $source, array $row): void
    {
        $type = MentionTypeEnum::from($row['mention_type']);

        if ($type === MentionTypeEnum::User && $row['user_id']) {
            User::query()->whereKey($row['user_id'])->first()
                ?->notify(new MentionNotification($source));

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

        if ($target instanceof IsSubscribable) {
            $target->subscribeNotificationChannel(
                channel: method_exists($source, 'broadcastChannel')
                    ? $source->broadcastChannel()
                    : $source->getMorphClass() . '.' . $source->getKey(),
                event: 'eloquent.created: ' . $source::class,
            );
        }
    }
}
