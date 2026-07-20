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
     * narrowed down to a single related record.
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

    public function performAction(): int
    {
        $parent = resolve_static(Product::class, 'query')
            ->whereKey($this->getData('parent_id'))
            ->firstOrFail(['id']);

        $variantIds = $parent->children()
            ->when(
                $this->getData('variant_ids'),
                fn (Builder $query, array $ids) => $query->whereIntegerInRaw('id', $ids)
            )
            ->pluck('id');

        if ($variantIds->isEmpty()) {
            return 0;
        }

        $touched = 0;

        foreach ($this->getData('relations') as $reset) {
            $touched += $this->resetRelation(
                $parent,
                $variantIds->all(),
                data_get($reset, 'relation'),
                data_get($reset, 'related_id')
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

        return $touched;
    }

    protected function resetRelation(Product $parent, array $variantIds, string $relation, mixed $relatedId): int
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
                    ! is_null($relatedId),
                    fn (QueryBuilder $query) => $query->where($relationInstance->getRelatedPivotKeyName(), $relatedId)
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
                ! is_null($relatedId),
                fn (Builder $query) => $query
                    ->where($this->resolveRelatedIdColumn($relation), $relatedId)
            );

        $touched = $query
            ->clone()
            ->distinct()
            ->count($relationInstance->getForeignKeyName());

        $query->delete();

        return $touched;
    }

    protected function resolveRelatedIdColumn(string $relation): string
    {
        return static::relatedIdColumns()[$relation];
    }

    protected function validateData(): void
    {
        parent::validateData();

        $this->validateVariantParentage('resetProductRelations');

        $product = app(Product::class);

        foreach ($this->getData('relations') as $index => $reset) {
            $relation = data_get($reset, 'relation');
            $relationInstance = $product->{'own' . ucfirst($relation)}();

            if (! $relationInstance instanceof BelongsToMany && ! $relationInstance instanceof HasMany) {
                throw ValidationException::withMessages([
                    'relations.' . $index . '.relation' => [
                        'Unsupported relation type for reset: [' . $relation . '].',
                    ],
                ])
                    ->errorBag('resetProductRelations');
            }

            if (
                ! is_null(data_get($reset, 'related_id'))
                && $relationInstance instanceof HasMany
                && ! array_key_exists($relation, static::relatedIdColumns())
            ) {
                throw ValidationException::withMessages([
                    'relations.' . $index . '.related_id' => [
                        'No related id column mapping for [' . $relation . '].',
                    ],
                ])
                    ->errorBag('resetProductRelations');
            }
        }
    }
}
