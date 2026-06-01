<?php

namespace FluxErp\Traits\Model;

use FluxErp\Contracts\MentionsContent;
use FluxErp\Observers\RecordsMentionsObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait RecordsMentions
{
    public static function bootRecordsMentions(): void
    {
        static::saved(function (Model&MentionsContent $model): void {
            app(RecordsMentionsObserver::class)->saved($model);
        });

        static::deleted(function (Model&MentionsContent $model): void {
            app(RecordsMentionsObserver::class)->deleted($model);
        });
    }

    /**
     * @return Collection<int, object>|null
     */
    public function mentionableMembersScope(): ?Collection
    {
        return null;
    }

    public function mentionScannableText(): string
    {
        return collect($this->mentionableTextFields())
            ->map(fn (string $field): string => (string) $this->getAttribute($field))
            ->implode("\n");
    }
}
