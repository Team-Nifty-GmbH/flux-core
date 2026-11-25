<?php

namespace FluxErp\Actions\Document;

use FluxErp\Actions\FluxAction;
use FluxErp\Enums\DocumentReviewStateEnum;
use FluxErp\Models\Media;
use FluxErp\Rulesets\Document\DownloadDocumentsRuleset;
use FluxErp\Support\MediaLibrary\FluxMediaStream;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class DownloadDocuments extends FluxAction
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
        return DownloadDocumentsRuleset::class;
    }

    public function performAction(): FluxMediaStream
    {
        $media = resolve_static(Media::class, 'query')
            ->documents()
            ->where('model_type', $this->getData('model_type'))
            ->whereIntegerInRaw('model_id', $this->getData('model_ids'))
            ->where('review_state', DocumentReviewStateEnum::Approved)
            ->with(['documentType:id,name', 'model'])
            ->orderBy('document_date')
            ->get()
            ->each(function (Media $media): void {
                $label = $media->model instanceof InteractsWithDataTables
                    ? $media->model->getLabel()
                    : null;

                $media->setCustomProperty(
                    'zip_filename_prefix',
                    collect([$label ?: $media->model_id, $media->document_date?->year, $media->documentType?->name])
                        ->map(fn (mixed $segment) => str_replace(['/', '\\'], '-', (string) $segment))
                        ->implode('/') . '/'
                );
            });

        return FluxMediaStream::create('documents.zip')->addMedia($media);
    }
}
