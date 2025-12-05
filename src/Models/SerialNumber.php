<?php

namespace FluxErp\Models;

use Exception;
use FluxErp\Traits\Model\Commentable;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasFrontendAttributes;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\InteractsWithMedia;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Scout\Searchable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Spatie\MediaLibrary\HasMedia;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class SerialNumber extends FluxModel implements HasMedia, InteractsWithDataTables
{
    use Commentable, Filterable, HasFrontendAttributes, HasPackageFactory, HasUserModification, HasUuid,
        InteractsWithMedia, LogsActivity;
    use Searchable {
        Searchable::scoutIndexSettings as baseScoutIndexSettings;
    }

    public static string $iconName = 'tag';

    protected ?string $detailRouteName = 'products.serial-numbers.id?';

    public static function scoutIndexSettings(): ?array
    {
        return static::baseScoutIndexSettings() ?? [
            'filterableAttributes' => [
                'address_id',
            ],
        ];
    }

    public function addresses(): BelongsToMany
    {
        return $this->belongsToMany(Address::class, 'address_serial_number')->withPivot('quantity');
    }

    /**
     * @throws Exception
     */
    public function getAvatarUrl(): ?string
    {
        return $this->getFirstMediaUrl('avatar') ?: static::icon()->getUrl();
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getLabel(): ?string
    {
        return $this->serial_number . ' - ' . $this->product?->name;
    }

    public function getUrl(): ?string
    {
        return $this->detailRoute();
    }

    public function product(): HasOneThrough
    {
        return $this->hasOneThrough(Product::class, StockPosting::class, 'serial_number_id', 'id', 'id', 'product_id');
    }

    public function stockPostings(): HasMany
    {
        return $this->hasMany(StockPosting::class);
    }
}
