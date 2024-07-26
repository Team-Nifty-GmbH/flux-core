<?php

namespace FluxErp\Models;

use FluxErp\Traits\Categorizable;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;
use FluxErp\Traits\Scout\Searchable;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\ModelInfo\ModelInfo;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Category extends Model implements InteractsWithDataTables, Sortable
{
    use Commentable, Filterable, HasAdditionalColumns, HasPackageFactory, HasUserModification,
        HasUuid, Searchable, SortableTrait;

    protected $guarded = [
        'id',
    ];

    protected $hidden = [
        'pivot',
    ];

    public $translatable = [
        'name',
    ];

    public array $sortable = [
        'order_column_name' => 'sort_number',
        'sort_when_creating' => true,
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function booted(): void
    {
        model_info_all()
            ->filter(fn (ModelInfo $modelInfo) => $modelInfo->traits->contains(Categorizable::class))
            ->each(function (ModelInfo $modelInfo) {
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

    public function assigned(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->model()?->count(),
        );
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->with('children');
    }

    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'category_price_list');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
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

    public function getAvatarUrl(): ?string
    {
        return null;
    }
}
