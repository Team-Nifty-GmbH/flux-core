<?php

namespace FluxErp\Models;

use FluxErp\Traits\Commentable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasFrontendAttributes;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\Scout\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class SerialNumber extends Model implements HasMedia, InteractsWithDataTables
{
    use Commentable, Filterable, HasAdditionalColumns, HasFrontendAttributes, HasPackageFactory, HasUserModification,
        HasUuid, InteractsWithMedia, Searchable;

    protected $guarded = [
        'id',
    ];

    public string $detailRouteName = 'products.serial-numbers.id?';

    public static string $iconName = 'tag';

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderPosition(): BelongsTo
    {
        return $this->belongsTo(OrderPosition::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with(['product', 'address']);
    }

    public function getLabel(): ?string
    {
        return $this->serial_number . ' - ' . $this->product?->name;
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getUrl(): ?string
    {
        return $this->detailRoute();
    }

    /**
     * @throws \Exception
     */
    public function getAvatarUrl(): ?string
    {
        return $this->getFirstMediaUrl('avatar') ?: self::icon()->getUrl();
    }
}
