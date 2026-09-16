<?php

namespace FluxErp\Actions\Product;

use FluxErp\Actions\DispatchableFluxAction;
use FluxErp\Models\AttributeTranslation;
use FluxErp\Models\Product;
use FluxErp\Rulesets\Product\SyncVariantInheritanceRuleset;
use FluxErp\Settings\ProductSettings;
use FluxErp\Traits\Action\ValidatesVariantParentage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class SyncVariantInheritance extends DispatchableFluxAction
{
    use ValidatesVariantParentage;

    // Internal propagation step, only reached through the actions, the model hooks and
    // the migration job, which carry the user facing permission themselves.
    protected static bool $hasPermission = false;

    public static function models(): array
    {
        return [Product::class];
    }

    protected function getRulesets(): string|array
    {
        return SyncVariantInheritanceRuleset::class;
    }

    public function middleware(): array
    {
        return [new WithoutOverlapping('variant-sync:' . $this->getData('parent_id'))];
    }

    public function performAction(): ?bool
    {
        if (! app(ProductSettings::class)->variant_inheritance_enabled) {
            return null;
        }

        $fields = $this->getData('fields') ?: app(Product::class)->getInheritableFields();

        /** @var Product|null $parent */
        $parent = resolve_static(Product::class, 'query')
            ->whereKey($this->getData('parent_id'))
            ->first(array_merge(['id'], $fields));

        if (! $parent) {
            return null;
        }

        foreach ($fields as $field) {
            $this->variantQuery($parent)
                ->where(fn (Builder $query) => $query
                    ->whereNull('overridden_fields')
                    ->orWhereJsonDoesntContain('overridden_fields', $field)
                )
                ->update([$field => $parent->getRawOriginal($field)]);
        }

        $this->syncTranslations($parent, $fields);

        // Query-builder updates bypass Scout's ModelObserver — reindex explicitly.
        $this->variantQuery($parent)->searchable();

        return true;
    }

    protected function syncTranslations(Product $parent, array $fields): void
    {
        $fields = array_intersect($fields, resolve_static(Product::class, 'getTranslatableAttributes'));

        if (! $fields) {
            return;
        }

        $modelType = $parent->getMorphClass();

        foreach ($fields as $field) {
            $variantIds = $this->variantQuery($parent)
                ->where(fn (Builder $query) => $query
                    ->whereNull('overridden_fields')
                    ->orWhereJsonDoesntContain('overridden_fields', $field)
                )
                ->pluck('id');

            if ($variantIds->isEmpty()) {
                continue;
            }

            $parentTranslations = resolve_static(AttributeTranslation::class, 'query')
                ->where('model_type', $modelType)
                ->whereIntegerInRaw('model_id', [$parent->getKey()])
                ->where('attribute', $field)
                ->pluck('value', 'language_id');

            // Drop variant rows for locales the parent no longer has (all of them, if none are left).
            resolve_static(AttributeTranslation::class, 'query')
                ->when(
                    $parentTranslations->isNotEmpty(),
                    fn (Builder $query) => $query
                        ->whereIntegerNotInRaw('language_id', $parentTranslations->keys())
                )
                ->where('model_type', $modelType)
                ->whereIntegerInRaw('model_id', $variantIds)
                ->where('attribute', $field)
                ->delete();

            foreach ($parentTranslations as $languageId => $value) {
                $existingVariantIds = resolve_static(AttributeTranslation::class, 'query')
                    ->where('language_id', $languageId)
                    ->where('model_type', $modelType)
                    ->whereIntegerInRaw('model_id', $variantIds)
                    ->where('attribute', $field)
                    ->pluck('model_id');

                if ($existingVariantIds->isNotEmpty()) {
                    resolve_static(AttributeTranslation::class, 'query')
                        ->where('language_id', $languageId)
                        ->where('model_type', $modelType)
                        ->whereIntegerInRaw('model_id', $existingVariantIds)
                        ->where('attribute', $field)
                        ->update(['value' => $value]);
                }

                $missingVariantIds = $variantIds->diff($existingVariantIds);

                if ($missingVariantIds->isNotEmpty()) {
                    $missingVariantIds->each(
                        fn (int $variantId) => resolve_static(AttributeTranslation::class, 'query')
                            ->create([
                                'language_id' => $languageId,
                                'model_type' => $modelType,
                                'model_id' => $variantId,
                                'attribute' => $field,
                                'value' => $value,
                            ])
                    );
                }
            }
        }
    }

    protected function validateData(): void
    {
        parent::validateData();

        $this->validateVariantParentage('syncVariantInheritance');
    }

    protected function variantQuery(Product $parent): Builder
    {
        return resolve_static(Product::class, 'query')
            ->where('parent_id', $parent->getKey())
            ->when(
                $this->getData('variant_ids'),
                fn (Builder $query, array $variantIds) => $query->whereKey($variantIds)
            );
    }
}
