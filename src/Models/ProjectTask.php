<?php

namespace FluxErp\Models;

use FluxErp\States\ProjectTask\ProjectTaskState;
use FluxErp\Traits\Categorizable;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasFrontendAttributes;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\SoftDeletes;
use FluxErp\Traits\Trackable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MediaLibraryMedia;
use Spatie\ModelStates\HasStates;
use Spatie\Tags\HasTags;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;

class ProjectTask extends Model implements HasMedia
{
    use BroadcastsEvents, Categorizable, Commentable, Filterable, HasAdditionalColumns, HasFrontendAttributes,
        HasPackageFactory, HasStates, HasTags, HasUserModification, HasUuid, InteractsWithMedia, Searchable,
        SoftDeletes, Trackable;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'is_done' => 'boolean',
        'is_paid' => 'boolean',
        'state' => ProjectTaskState::class,
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
        return $this->with('address:id,company,firstname,lastname,city')
            ->with('project:id,project_name,display_name')
            ->whereKey($this->id)
            ->first()?->toArray() ?? [];
    }
}
