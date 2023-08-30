<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasUserModification;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{
    use HasUserModification;

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

    public function getThumbnailAttribute(): ?string
    {
        return $this->hasGeneratedConversion('thumb') ?
            $this->getUrl() . '&thumb=true' : null;
    }

    public function temporaryUpload(): BelongsTo
    {
        // When using the base method from spatie media this method throws an exception.
        // Thats why we override the method here and return an empty BelongsTo.
        return new BelongsTo(self::query(), new self, '', '', '');
    }
}
