<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\BankConnectionTenant;
use FluxErp\Models\Pivots\PaymentTypeTenant;
use FluxErp\Models\Pivots\ProductTenant;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasAttributeTranslations;
use FluxErp\Traits\Model\HasDefault;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasTenantAssignment;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\InteractsWithMedia;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use FluxErp\Traits\Scout\Searchable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\Engine;
use Spatie\MediaLibrary\HasMedia;

class Tenant extends FluxModel implements HasMedia
{
    use Filterable, HasAttributeTranslations, HasDefault, HasPackageFactory, HasTenantAssignment, HasUserModification,
        HasUuid, InteractsWithMedia, LogsActivity, Searchable, SoftDeletes;

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

    // Relations
    public function bankConnections(): BelongsToMany
    {
        return $this->belongsToMany(BankConnection::class, 'bank_connection_tenant')
            ->using(BankConnectionTenant::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function paymentTypes(): BelongsToMany
    {
        return $this->belongsToMany(PaymentType::class, 'payment_type_tenant')
            ->using(PaymentTypeTenant::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_tenant')
            ->using(ProductTenant::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    // Public methods
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

    // Attributes
    protected function avatarUrl(): Attribute
    {
        return $this->logoSmallUrl();
    }

    protected function postalAddressOneLine(): Attribute
    {
        return Attribute::get(
            fn (mixed $value, array $attributes) => implode(
                ' | ',
                array_filter([
                    $attributes['name'] ?? null,
                    $attributes['street'] ?? null,
                    trim($attributes['postcode'] ?? null . ' ' . $attributes['city'] ?? null),
                ])
            )
        );
    }

    protected function logoSmallUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->getFirstMediaUrl('logo_small')
        );
    }

    protected function logoUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->getFirstMediaUrl('logo')
        );
    }

    // Protected methods
    protected function translatableAttributes(): array
    {
        return [
            'sepa_text_basic',
            'sepa_text_b2b',
            'terms_and_conditions',
        ];
    }
}
