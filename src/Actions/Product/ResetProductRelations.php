<?php

namespace FluxErp\Actions\Product;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Product;
use FluxErp\Rulesets\Product\ResetProductRelationsRuleset;
use FluxErp\Support\VariantInheritance\PivotInheritanceSync;
use FluxErp\Traits\Action\ValidatesVariantParentage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Validation\ValidationException;

class ResetProductRelations extends FluxAction
{
    use ValidatesVariantParentage;

    public static function models(): array
    {
        return [Product::class];
    }

    /**
     * Maps a HasMany relation to the column carrying the related id, so a reset can be
     * narrowed down to single related records.
     */
    protected static function relatedIdColumns(): array
    {
        return [
            'prices' => 'price_list_id',
        ];
    }

    protected function getRulesets(): string|array
    {
        return ResetProductRelationsRuleset::class;
    }

    public function performAction(): Product
    {
        $parent = resolve_static(Product::class, 'query')
            ->whereKey($this->getData('parent_id'))
            ->firstOrFail(['id']);

        $variantIds = $parent->children()
            ->when(
                $this->getData('variant_ids'),
                fn (Builder $query, array $ids) => $query->whereKey($ids)
            )
            ->pluck('id');

        if ($variantIds->isEmpty()) {
            return $parent;
        }

        $touched = 0;

        foreach ($this->getData('relations') as $reset) {
            $touched += $this->resetRelation(
                $parent,
                $variantIds->all(),
                data_get($reset, 'relation'),
                data_get($reset, 'related_ids') ?? []
            );
        }

        if ($touched > 0) {
            // Re-materialize the parent's rows as is_inherited=true copies on the
            // now non-owning variants. Price propagation runs through the saved()
            // hook on the price model; pivot relations through the pivot sync.
            $parent->ownPrices()
                ->get()
                ->each
                ->save();

            resolve_static(PivotInheritanceSync::class, 'propagateToChildren', ['parent' => $parent]);
        }

        return $parent->refresh();
    }

    protected function resetRelation(Product $parent, array $variantIds, string $relation, array $relatedIds): int
    {
        // The relation instance is only used for its metadata, so reuse the already
        // loaded parent instead of querying a variant per relation.
        $relationInstance = $parent->{'own' . ucfirst($relation)}();

        if ($relationInstance instanceof BelongsToMany) {
            $query = $relationInstance
                ->newPivotStatement()
                ->whereIntegerInRaw($relationInstance->getForeignPivotKeyName(), $variantIds)
                ->when(
                    $relationInstance instanceof MorphToMany,
                    fn (QueryBuilder $query) => $query
                        ->where($relationInstance->getMorphType(), $relationInstance->getMorphClass())
                )
                ->when(
                    $relatedIds,
                    fn (QueryBuilder $query) => $query
                        ->whereIntegerInRaw($relationInstance->getRelatedPivotKeyName(), $relatedIds)
                );

            $touched = $query
                ->clone()
                ->distinct()
                ->count($relationInstance->getForeignPivotKeyName());

            $query->delete();

            return $touched;
        }

        // validateData() already rejected anything that is neither BelongsToMany nor HasMany.
        $query = $relationInstance->getRelated()::query()
            ->whereIntegerInRaw($relationInstance->getForeignKeyName(), $variantIds)
            ->when(
                $relatedIds,
                fn (Builder $query) => $query
                    ->whereIntegerInRaw(static::relatedIdColumns()[$relation], $relatedIds)
            );

        $touched = $query
            ->clone()
            ->distinct()
            ->count($relationInstance->getForeignKeyName());

        $query->delete();

        return $touched;
    }

    protected function validateData(): void
    {
        parent::validateData();

        $this->validateVariantParentage('resetProductRelations');

        $product = app(Product::class);
        $errors = [];

        foreach ($this->getData('relations') as $index => $reset) {
            $relation = data_get($reset, 'relation');
            $relationInstance = $product->{'own' . ucfirst($relation)}();

            if (! $relationInstance instanceof BelongsToMany && ! $relationInstance instanceof HasMany) {
                $errors['relations.' . $index . '.relation'] = [
                    'Unsupported relation type for reset: [' . $relation . '].',
                ];

                continue;
            }

            if (
                data_get($reset, 'related_ids')
                && $relationInstance instanceof HasMany
                && ! array_key_exists($relation, static::relatedIdColumns())
            ) {
                $errors['relations.' . $index . '.related_ids'] = [
                    'No related id column mapping for [' . $relation . '].',
                ];
            }
        }

        // one response carrying every offending entry instead of stopping at the first
        if ($errors) {
            throw ValidationException::withMessages($errors)
                ->errorBag('resetProductRelations');
        }
    }
}
