<?php

namespace FluxErp\Actions\Document;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Media;
use FluxErp\Models\Scopes\ExcludeDocumentsScope;
use FluxErp\Rulesets\Document\DownloadDocumentRuleset;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadDocument extends FluxAction
{
    public static function models(): array
    {
        return [Media::class];
    }

    protected static function getReturnResult(): bool
    {
        return parent::getReturnResult() ?? true;
    }

    protected function getRulesets(): string|array
    {
        return DownloadDocumentRuleset::class;
    }

    public function performAction(): StreamedResponse
    {
        return resolve_static(Media::class, 'query')
            ->withoutGlobalScope(ExcludeDocumentsScope::class)
            ->whereKey($this->getData('id'))
            ->firstOrFail()
            ->toResponse(request());
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (! static::canPerformAction(false)
            && ! resolve_static(Media::class, 'query')
                ->withoutGlobalScope(ExcludeDocumentsScope::class)
                ->whereKey($this->getData('id'))
                ->first()
                ->model
                ?->isDocumentOwnedBy(auth()->user())
        ) {
            throw UnauthorizedException::forPermissions(['action.' . static::name()]);
        }
    }
}
