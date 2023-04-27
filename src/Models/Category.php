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
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Category extends Model implements Sortable
{
    use Commentable, Filterable, HasAdditionalColumns, HasPackageFactory, HasUserModification,
        HasUuid, SortableTrait;

    protected $appends = [
        'assigned',
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
}
