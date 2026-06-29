<?php

namespace FluxErp\Traits\Model;

use FluxErp\Contracts\MentionsContent;
use FluxErp\Observers\RecordsMentionsObserver;
use FluxErp\Support\Mentions\MentionHtml;
use Illuminate\Database\Eloquent\Model;

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

    public function mentionScannableText(): string
    {
        return collect($this->mentionableColumns())
            ->map(fn (string $column): string => MentionHtml::toTokens((string) $this->getAttribute($column)))
            ->implode("\n");
    }
}
