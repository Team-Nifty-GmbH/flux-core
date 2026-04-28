<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\CategoryPriceList;
use FluxErp\Traits\Model\Categorizable;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasAttributeTranslations;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasParentChildRelations;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SortableTrait;
use FluxErp\Traits\Scout\Searchable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use Spatie\EloquentSortable\Sortable;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Category extends FluxModel implements InteractsWithDataTables, Sortable
{
    use Filterable, HasAttributeTranslations, HasPackageFactory, HasParentChildRelations, HasUserModification, HasUuid,
        LogsActivity, SortableTrait;
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

    protected static function booted(): void
    {
        collect(Relation::morphMap())
            ->filter(fn (string $class) => in_array(
                Categorizable::class,
                class_uses_recursive($class)
            ))
            ->each(function (string $class): void {
                $resolved = resolve_static($class, 'class');
                $relationName = Str::of(class_basename($resolved))->camel()->plural()->toString();

                if (method_exists(static::class, $relationName)) {
                    return;
                }

                static::resolveRelationUsing(
                    $relationName,
                    function (Category $category) use ($resolved) {
                        return $category->morphedByMany($resolved, 'categorizable', 'categorizable')
                            ->using(Pivots\Categorizable::class);
                    }
                );
            });
    }

    // Public static methods
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

    // Relations
    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'category_price_list')
            ->using(CategoryPriceList::class);
    }

    public function model(): MorphToMany
    {
        return $this->model_type
            ? $this->morphedByMany(morphed_model($this->model_type), 'categorizable', 'categorizable')
                ->using(Pivots\Categorizable::class)
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

    // Public methods
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

    // Attributes
    protected function assigned(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->model()?->count(),
        );
    }

    // Protected methods
    protected function translatableAttributes(): array
    {
        return [
            'name',
        ];
    }
}
