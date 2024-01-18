<?php

namespace FluxErp\Models;

use FluxErp\Traits\Commentable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;

class Client extends Model implements HasMedia
{
    use Commentable, Filterable, HasPackageFactory, HasUserModification, HasUuid, InteractsWithMedia, SoftDeletes;

    protected $appends = [
        'logo_url',
        'logo_small_url',
    ];

    protected $casts = [
        'uuid' => 'string',
        'is_active' => 'boolean',
        'opening_hours' => 'array',
        'is_default' => 'boolean',
    ];

    protected $guarded = [
        'id',
    ];

    protected $with = [
        'media',
    ];

    public function getAvatarUrlAttribute(): string
    {
        return $this->logo_small_url;
    }

    public function logoUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->getFirstMediaUrl('logo')
        );
    }

    public function logoSmallUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->getFirstMediaUrl('logo_small')
        );
    }

    public function bankConnections(): BelongsToMany
    {
        return $this->belongsToMany(BankConnection::class, 'bank_connection_client');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function settings(): MorphMany
    {
        return $this->morphMany(Setting::class, 'model');
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

    public static function default(): ?static
    {
        return static::query()->where('is_default', true)->first();
    }
}
