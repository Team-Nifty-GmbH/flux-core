<?php

namespace FluxErp\Models;

use FluxErp\Traits\Commentable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Laravel\Scout\Searchable;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Category extends Model implements Sortable, InteractsWithDataTables
{
    use Commentable, Filterable, HasAdditionalColumns, HasPackageFactory, HasUserModification,
        HasUuid, Searchable, SortableTrait;

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $guarded = [
        'id',
        'uuid',
    ];

    protected $hidden = [
        'uuid',
        'pivot',
    ];

    public $translatable = [
        'name',
    ];

    public array $sortable = [
        'order_column_name' => 'sort_number',
        'sort_when_creating' => true,
    ];

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
            ? $this->morphedByMany($this->model_type, 'categorizable')
            : new MorphToMany(
                self::query(),
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
