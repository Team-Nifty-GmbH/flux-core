<?php

namespace FluxErp\Actions\Document;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Media;
use FluxErp\Models\Scopes\ExcludeDocumentsScope;
use FluxErp\Rulesets\Document\DeleteDocumentRuleset;
use Illuminate\Validation\ValidationException;

class DeleteDocument extends FluxAction
{
    public static function models(): array
    {
        return [Media::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteDocumentRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(Media::class, 'query')
            ->withoutGlobalScope(ExcludeDocumentsScope::class)
            ->whereKey($this->getData('id'))
            ->firstOrFail()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(Media::class, 'query')
            ->withoutGlobalScope(ExcludeDocumentsScope::class)
            ->whereKey($this->getData('id'))
            ->first()
            ->isLocked()
        ) {
            throw ValidationException::withMessages([
                'id' => [__('Documents are kept until their retention period ends.')],
            ]);
        }
    }
}
