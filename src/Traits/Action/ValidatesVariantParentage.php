<?php

namespace FluxErp\Traits\Action;

use FluxErp\Models\Product;
use Illuminate\Validation\ValidationException;

trait ValidatesVariantParentage
{
    /**
     * Reject variant ids that are not children of the given parent product, instead of
     * silently ignoring them when the action scopes its query to the parent's children.
     */
    protected function validateVariantParentage(string $errorBag): void
    {
        $variantIds = $this->getData('variant_ids');

        if (! $variantIds) {
            return;
        }

        $foreignIds = array_values(
            array_diff(
                $variantIds,
                resolve_static(Product::class, 'query')
                    ->whereKey($variantIds)
                    ->where('parent_id', $this->getData('parent_id'))
                    ->pluck('id')
                    ->all()
            )
        );

        if ($foreignIds) {
            throw ValidationException::withMessages([
                'variant_ids' => [
                    'Variants do not belong to the given parent product: [' . implode(', ', $foreignIds) . '].',
                ],
            ])
                ->errorBag($errorBag);
        }
    }
}
