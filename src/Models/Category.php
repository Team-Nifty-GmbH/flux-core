<?php

namespace FluxErp\Models;

use FluxErp\Traits\Categorizable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasAttributeTranslations;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasParentChildRelations;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\Scout\Searchable;
use FluxErp\Traits\SortableTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;
use Spatie\EloquentSortable\Sortable;
use Spatie\ModelInfo\ModelInfo;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Category extends FluxModel implements InteractsWithDataTables, Sortable
{
    use Filterable, HasAdditionalColumns, HasAttributeTranslations, HasPackageFactory, HasParentChildRelations,
        HasUserModification, HasUuid, LogsActivity, SortableTrait;
    use Searchable {
        Searchable::scoutIndexSettings as baseScoutIndexSettings;
    }

    public array $sortable = [
        'order_column_name' => 'sort_number',
        'sort_when_creating' => true,
    ];

    protected $hidden = [
        'pivot',
    ];

    public static function booted(): void
    {
        model_info_all()
            ->filter(fn (ModelInfo $modelInfo) => in_array(
                Categorizable::class,
                class_uses_recursive($modelInfo->class)
            ))
            ->each(function (ModelInfo $modelInfo): void {
                $relationName = Str::of(class_basename($modelInfo->class))->camel()->plural()->toString();

                if (method_exists(static::class, $relationName)) {
                    return;
                }

                static::resolveRelationUsing(
                    $relationName,
                    function (Category $category) use ($modelInfo) {
                        return $category->morphedByMany($modelInfo->class, 'categorizable');
                    }
                );
            });
    }

    public static function scoutIndexSettings(): ?array
    {
        return static::baseScoutIndexSettings() ?? [
            'filterableAttributes' => [
                'model_type',
            ],
        ];
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function assigned(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->model()?->count(),
        );
    }

    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'category_price_list');
    }

    public function getAvatarUrl(): ?string
    {
        return null;
    }

    public function getDescription(): ?string
    {
        return $this->name;
    }

    public function getLabel(): ?string
    {
        $path = [$this->name];

        $parent = $this->parent;
        while ($parent) {
            $path[] = $parent->name;
            $parent = $parent->parent;
        }

        return implode(' / ', array_reverse($path));
    }

    public function getUrl(): ?string
    {
        return null;
    }

    public function model(): MorphToMany
    {
        return $this->model_type
            ? $this->morphedByMany(morphed_model($this->model_type), 'categorizable')
            : new MorphToMany(
                static::query(),
                $this,
                '',
                '',
                '',
                '',
                '',
                ''
            );
    }

    public function priceLists(): BelongsToMany
    {
        return $this->belongsToMany(PriceList::class, 'category_price_list');
    }

    protected function translatableAttributes(): array
    {
        return [
            'name',
        ];
    }
}
