<?php

namespace FluxErp\Actions\Document;

use FluxErp\Actions\FluxAction;
use FluxErp\Enums\DocumentReviewStateEnum;
use FluxErp\Models\Media;
use FluxErp\Models\Scopes\ExcludeDocumentsScope;
use FluxErp\Rulesets\Document\ReviewDocumentRuleset;
use FluxErp\Support\Media\DocumentRetention;

class ReviewDocument extends FluxAction
{
    public static function models(): array
    {
        return [Media::class];
    }

    protected function getRulesets(): string|array
    {
        return ReviewDocumentRuleset::class;
    }

    public function performAction(): Media
    {
        $media = resolve_static(Media::class, 'query')
            ->withoutGlobalScope(ExcludeDocumentsScope::class)
            ->whereKey($this->getData('id'))
            ->firstOrFail();

        $media->forceFill([
            'review_state' => $this->getData('review_state'),
            'review_note' => $this->getData('review_note'),
            'reviewed_at' => now(),
            'reviewed_by' => auth()->check() ? auth()->user()->getMorphClass() . ':' . auth()->id() : null,
        ]);

        if ($media->review_state === DocumentReviewStateEnum::Approved) {
            $media->retain_until = app(DocumentRetention::class)($media);
        }

        $media->save();

        return $media->refresh();
    }
}
