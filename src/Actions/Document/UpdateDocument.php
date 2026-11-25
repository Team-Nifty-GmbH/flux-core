<?php

namespace FluxErp\Actions\Document;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Media;
use FluxErp\Models\Scopes\ExcludeDocumentsScope;
use FluxErp\Rulesets\Document\UpdateDocumentRuleset;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class UpdateDocument extends FluxAction
{
    public static function models(): array
    {
        return [Media::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateDocumentRuleset::class;
    }

    public function performAction(): Media
    {
        $media = resolve_static(Media::class, 'query')
            ->withoutGlobalScope(ExcludeDocumentsScope::class)
            ->whereKey($this->getData('id'))
            ->firstOrFail();

        $media->forceFill(['retain_until' => $this->getData('retain_until')])->save();

        return $media->refresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $retainUntil = resolve_static(Media::class, 'query')
            ->withoutGlobalScope(ExcludeDocumentsScope::class)
            ->whereKey($this->getData('id'))
            ->value('retain_until');

        if ($retainUntil && Carbon::parse($this->getData('retain_until'))->lt($retainUntil)) {
            throw ValidationException::withMessages([
                'retain_until' => [__('The retention period can only be extended.')],
            ]);
        }
    }
}
