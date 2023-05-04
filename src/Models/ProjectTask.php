<?php

namespace FluxErp\Models;

use FluxErp\Traits\Categorizable;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MediaLibraryMedia;

class ProjectTask extends Model implements HasMedia
{
    use Categorizable, Commentable, Filterable, HasAdditionalColumns, HasPackageFactory, HasUserModification,
        HasUuid, InteractsWithMedia, Searchable, SoftDeletes;

    protected $guarded = [
        'id',
        'uuid',
    ];

    protected $casts = [
        'is_done' => 'boolean',
        'is_paid' => 'boolean',
    ];

    protected $hidden = [
        'uuid',
    ];

    public array $translatable = [
        'name',
    ];

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function orderPosition(): BelongsTo
    {
        return $this->belongsTo(OrderPosition::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public function registerMediaConversions(MediaLibraryMedia $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(368)
            ->height(232)
            ->optimize()
            ->nonQueued()
            ->performOnCollections('files');
    }

    public function toSearchableArray(): array
    {
        return $this->with('address')
            ->with('project')
            ->whereKey($this->id)
            ->first()?->toArray() ?? [];
    }
}
