<?php

namespace FluxErp\Actions\Document;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Enums\DocumentReviewStateEnum;
use FluxErp\Models\DocumentType;
use FluxErp\Models\Media;
use FluxErp\Rulesets\Document\UploadDocumentRuleset;
use FluxErp\Support\Media\DocumentRetention;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;

class UploadDocument extends FluxAction
{
    public static function models(): array
    {
        return [Media::class];
    }

    protected function getRulesets(): string|array
    {
        return UploadDocumentRuleset::class;
    }

    public function performAction(): Media
    {
        $media = UploadMedia::make(Arr::whereNotNull([
            'model_type' => $this->getData('model_type'),
            'model_id' => $this->getData('model_id'),
            'media' => $this->getData('media'),
            'name' => $this->getData('name'),
            'collection_name' => 'documents',
        ]))
            ->validate()
            ->execute();

        $isReviewer = resolve_static(ReviewDocument::class, 'canPerformAction', [false]);

        $media->forceFill([
            'document_type_id' => $this->getData('document_type_id'),
            'document_date' => $this->getData('document_date'),
            'review_state' => $isReviewer ? DocumentReviewStateEnum::Approved : DocumentReviewStateEnum::Pending,
            'reviewed_at' => $isReviewer ? now() : null,
            'reviewed_by' => $isReviewer && auth()->check()
                ? auth()->user()->getMorphClass() . ':' . auth()->id()
                : null,
        ]);
        $media->retain_until = $isReviewer ? app(DocumentRetention::class)($media) : null;
        $media->save();

        return $media->refresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['document_date'] ??= today()->toDateString();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $model = morph_to($this->getData('model_type'), $this->getData('model_id'));

        if (! static::canPerformAction(false) && ! $model->isDocumentOwnedBy(auth()->user())) {
            throw UnauthorizedException::forPermissions(['action.' . static::name()]);
        }

        $documentType = resolve_static(DocumentType::class, 'query')
            ->whereKey($this->getData('document_type_id'))
            ->first();

        if (! $documentType->isAllowedFor($this->getData('model_type'))
            || (
                ! $documentType->is_owner_uploadable
                && ! resolve_static(ReviewDocument::class, 'canPerformAction', [false])
            )
        ) {
            throw ValidationException::withMessages([
                'document_type_id' => [__('This document type cannot be uploaded here.')],
            ]);
        }
    }
}
