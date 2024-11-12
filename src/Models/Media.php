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

    public ?string $path = null;

    public bool $isTemporary = false;

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

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function setIsTemporary(bool $isTemporary = true): static
    {
        $this->isTemporary = $isTemporary;

        return $this;
    }

    public function getPath(string $conversionName = ''): string
    {
        return $this->path ?? parent::getPath($conversionName);
    }

    public function stream()
    {
        if ($this->path) {
            // return a resource
            return fopen($this->path, 'rb');
        }

        return parent::stream();
    }
}
