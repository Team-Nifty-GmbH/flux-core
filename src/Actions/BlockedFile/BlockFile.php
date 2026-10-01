<?php

namespace FluxErp\Actions\BlockedFile;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\BlockedFile;
use FluxErp\Models\Media;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Rulesets\BlockedFile\BlockFileRuleset;
use Illuminate\Validation\ValidationException;

class BlockFile extends FluxAction
{
    public static function models(): array
    {
        return [BlockedFile::class];
    }

    protected function getRulesets(): string|array
    {
        return BlockFileRuleset::class;
    }

    public function performAction(): BlockedFile
    {
        $media = resolve_static(Media::class, 'query')
            ->whereKey($this->getData('media_id'))
            ->first();

        $blockedFile = resolve_static(BlockedFile::class, 'query')
            ->firstOrCreate(
                ['hash' => md5_file($media->getPath())],
                [
                    'file_name' => $media->file_name,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                ]
            );

        $media->forceDelete();

        return $blockedFile->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $media = resolve_static(Media::class, 'query')
            ->whereKey($this->getData('media_id'))
            ->first();

        $error = match (true) {
            ! is_readable($media->getPath()) => __('The file could not be read.'),
            data_get($media->getCollection(), 'readOnly') === true => __(
                'The media collection is read-only and cannot be modified.'
            ),
            resolve_static(PurchaseInvoice::class, 'query')
                ->where('media_id', $media->getKey())
                ->whereNotNull('order_id')
                ->exists() => __('The purchase invoice already has an order, its file cannot be blocked.'),
            default => null,
        };

        if ($error) {
            throw ValidationException::withMessages(['media_id' => [$error]])
                ->errorBag('blockFile');
        }
    }
}
