<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\Categorizable;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasFrontendAttributes;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\InteractsWithMedia;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use FluxErp\Traits\Scout\Searchable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\File;
use Spatie\MediaLibrary\HasMedia;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Resource extends FluxModel implements HasMedia, InteractsWithDataTables
{
    use Categorizable, Filterable, HasFrontendAttributes, HasPackageFactory, HasUserModification, HasUuid,
        InteractsWithMedia, LogsActivity, Searchable, SoftDeletes;

    public static string $iconName = 'cube';

    protected ?string $detailRouteName = 'resources.id?';

    protected function casts(): array
    {
        return [
            'allow_overbooking' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(ResourceBooking::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getAvatarUrl(): ?string
    {
        return $this->getFirstMediaUrl('avatar') ?: static::icon()->getUrl();
    }

    public function getDescription(): ?string
    {
        return $this->resource_number;
    }

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getUrl(): ?string
    {
        return $this->detailRoute();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->acceptsFile(function (File $file) {
                return str_starts_with($file->mimeType, 'image/');
            })
            ->useDisk('public')
            ->singleFile();
    }
}
