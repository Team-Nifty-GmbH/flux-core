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
use Illuminate\Support\Facades\Gate;

class RecordsMentionsObserver
{
    public function __construct(protected readonly MentionSync $sync) {}

    public function saved(Model&MentionsContent $source): void
    {
        $result = $this->sync->sync($source);

        foreach ($result['added'] as $row) {
            $this->dispatchForAddedRow($source, $row);
        }

        foreach ($result['removed'] as $mention) {
            $this->unsubscribeForRemovedMention($source, $mention);
        }
    }

    public function deleted(Model&MentionsContent $source): void
    {
        $mentions = resolve_static(Mention::class, 'query')
            ->where('mention_source_type', $source->getMorphClass())
            ->where('mention_source_id', $source->getKey())
            ->get();

        foreach ($mentions as $mention) {
            $this->unsubscribeForRemovedMention($source, $mention);
        }

        resolve_static(Mention::class, 'query')
            ->whereKey($mentions->modelKeys())
            ->delete();
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function dispatchForAddedRow(Model $source, array $row): void
    {
        $type = $row['mention_type_enum'];

        if ($type === MentionTypeEnum::User) {
            $notifiable = $this->resolveTarget($row['mention_target_type'], $row['mention_target_id'])
                ?? (
                    $row['user_id']
                        ? resolve_static(User::class, 'query')
                            ->whereKey($row['user_id'])
                            ->first()
                        : null
                );

            if ($notifiable && method_exists($notifiable, 'notify')) {
                $notification = $source instanceof ProvidesMentionNotification
                    ? $source->mentionNotification()
                    : app(MentionNotification::class, ['source' => $source]);

                $notifiable->notify($notification);
            }

            return;
        }

        if ($type !== MentionTypeEnum::Record) {
            return;
        }

        $target = $this->resolveTarget($row['mention_target_type'], $row['mention_target_id']);
        if (is_null($target) || ! $this->canMention($target)) {
            return;
        }

        if (method_exists($target, 'subscribeNotificationChannel')) {
            $target->subscribeNotificationChannel(
                channel: $this->subscriptionChannel($source),
                event: $this->subscriptionEvent($source),
            );
        }
    }

    protected function unsubscribeForRemovedMention(Model $source, Mention $mention): void
    {
        if ($mention->mention_type_enum?->value !== MentionTypeEnum::Record) {
            return;
        }

        $target = $this->resolveTarget($mention->mention_target_type, $mention->mention_target_id);

        if (! is_null($target) && method_exists($target, 'unsubscribeNotificationChannel')) {
            $target->unsubscribeNotificationChannel(
                $this->subscriptionChannel($source),
                $this->subscriptionEvent($source),
            );
        }
    }

    protected function canMention(Model $target): bool
    {
        $creator = auth()->user();

        if (is_null($creator) || is_null(Gate::getPolicyFor($target))) {
            return true;
        }

        return $creator->can('mention', $target);
    }

    protected function subscriptionChannel(Model $source): string
    {
        return method_exists($source, 'broadcastChannel')
            ? $source->broadcastChannel()
            : $source->getMorphClass() . '.' . $source->getKey();
    }

    protected function subscriptionEvent(Model $source): string
    {
        return method_exists($source, 'mentionSubscriptionEvent')
            ? $source->mentionSubscriptionEvent()
            : 'eloquent.created: ' . resolve_static($source::class, 'class');
    }

    protected function resolveTarget(?string $type, int|string|null $id): ?Model
    {
        if (! $type) {
            return null;
        }

        $targetClass = morphed_model($type);

        return $targetClass
            ? resolve_static($targetClass, 'query')
                ->whereKey($id)
                ->first()
            : null;
    }
}
