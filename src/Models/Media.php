<?php

namespace FluxErp\Models;

use FluxErp\Support\MediaLibrary\MediaCollection;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{
    use LogsActivity, ResolvesRelationsThroughContainer;

    protected $hidden = [
        'model_type',
        'model_id',
        'collection_name',
        'name',
        'disk',
        'conversions_disk',
        'manipulations',
        'responsive_images',
        'order_column',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function category(): MorphToMany
    {
        return $this->morphToMany(Category::class, 'categorizable');
    }

    public function temporaryUpload(): BelongsTo
    {
        // When using the base method from spatie media this method throws an exception.
        // Thats why we override the method here and return an empty BelongsTo.
        return new BelongsTo(static::query(), new static(), '', '', '');
    }

    public function getCollection(): ?MediaCollection
    {
        return $this->model->getMediaCollection($this->collection_name);
    }
}
