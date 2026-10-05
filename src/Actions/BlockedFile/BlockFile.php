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

        $blockedFile = app(BlockedFile::class, [
            'attributes' => [
                'hash' => md5_file($media->getPath()),
                'file_name' => $media->file_name,
                'mime_type' => $media->mime_type,
                'size' => $media->size,
            ],
        ]);
        $blockedFile->save();

        $media->forceDelete();

        return $blockedFile->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $media = resolve_static(Media::class, 'query')
            ->whereKey($this->getData('media_id'))
            ->first();

        $errors = [];

        if (! is_readable($media->getPath())) {
            $errors[] = 'The file could not be read.';
        } elseif (resolve_static(BlockedFile::class, 'isBlocked', ['hash' => md5_file($media->getPath())])) {
            $errors[] = 'The file is already blocked.';
        }

        if (data_get($media->getCollection(), 'readOnly') === true) {
            $errors[] = 'The media collection is read-only and cannot be modified.';
        }

        if (
            resolve_static(PurchaseInvoice::class, 'query')
                ->where('media_id', $media->getKey())
                ->whereNotNull('order_id')
                ->exists()
        ) {
            $errors[] = 'The purchase invoice already has an order, its file cannot be blocked.';
        }

        if ($errors) {
            throw ValidationException::withMessages(['media_id' => $errors])
                ->errorBag('blockFile');
        }
    }
}
