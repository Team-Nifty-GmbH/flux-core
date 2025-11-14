<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\TenantPaymentType;
use FluxErp\Traits\CacheModelQueries;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasDefault;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasTenantAssignment;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\Scout\Searchable;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\Engine;
use Spatie\MediaLibrary\HasMedia;

class Tenant extends FluxModel implements HasMedia
{
    use CacheModelQueries, Filterable, HasDefault, HasPackageFactory, HasTenantAssignment, HasUserModification, HasUuid,
        InteractsWithMedia, LogsActivity, Searchable, SoftDeletes;

    protected $table = 'tenants';

    protected $appends = [
        'logo_url',
        'logo_small_url',
    ];

    protected $with = [
        'media',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'opening_hours' => 'array',
            'is_default' => 'boolean',
        ];
    }

    public function bankConnections(): BelongsToMany
    {
        return $this->belongsToMany(BankConnection::class, 'bank_connection_tenant');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->logo_small_url;
    }

    public function getPostalAddressOneLineAttribute(): string
    {
        return implode(
            ' | ',
            array_filter([
                $this->name,
                $this->street,
                $this->postcode . ' ' . $this->city,
            ])
        );
    }

    public function logoSmallUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->getFirstMediaUrl('logo_small')
        );
    }

    public function logoUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->getFirstMediaUrl('logo')
        );
    }

    public function paymentTypes(): BelongsToMany
    {
        return $this->belongsToMany(PaymentType::class, 'tenant_payment_type')
            ->using(TenantPaymentType::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->useDisk('public')
            ->singleFile();

        $this->addMediaCollection('logo_small')
            ->useDisk('public')
            ->singleFile();
    }

    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('png')
            ->performOnCollections('logo', 'logo_small')
            ->format('png');
    }

    public function searchableUsing(): Engine
    {
        return app(EngineManager::class)->engine('collection');
    }
}
