<?php

namespace FluxErp\ShareTargetActions;

use FluxErp\Actions\PurchaseInvoice\CreatePurchaseInvoice;
use Illuminate\Validation\ValidationException;

class UploadPurchaseInvoice extends ShareTargetAction
{
    public static function accepts(?string $mimeType): bool
    {
        return in_array(
            $mimeType,
            [
                'application/pdf',
                'image/jpeg',
                'image/png',
                'application/xml',
                'text/xml',
            ],
            true
        );
    }

    public static function icon(): string
    {
        return 'document-arrow-up';
    }

    public static function label(): string
    {
        return __('Upload purchase invoice');
    }

    public static function supportsMultiple(): bool
    {
        return true;
    }

    public function handle(array $files): ?string
    {
        $exceptions = [];
        $created = 0;

        foreach ($files as $file) {
            try {
                CreatePurchaseInvoice::make(['media' => $file])
                    ->validate()
                    ->execute();

                $created++;
            } catch (ValidationException $e) {
                $exceptions[] = $e;
            }
        }

        if (! $created && $exceptions) {
            throw $exceptions[0];
        }

        return route('accounting.purchase-invoices', absolute: false);
    }
}
