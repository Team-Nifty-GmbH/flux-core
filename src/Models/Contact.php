<?php

namespace FluxErp\Models;

use FluxErp\Traits\BroadcastsEvents;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasFrontendAttributes;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasSerialNumberRange;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\Lockable;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Contact extends Model implements HasMedia, InteractsWithDataTables
{
    use BroadcastsEvents, Commentable, Filterable, HasPackageFactory, HasFrontendAttributes, HasSerialNumberRange,
        HasUserModification, HasUuid, InteractsWithMedia, Lockable, SoftDeletes;

    protected $hidden = [
        'uuid',
    ];

    protected $casts = [
        'uuid' => 'string',
        'has_sensitive_reminder' => 'boolean',
        'has_delivery_lock' => 'boolean',
    ];

    protected $guarded = [
        'id',
        'uuid',
    ];

    public static string $iconName = 'users';

    public static function boot()
    {
        parent::boot();

        static::creating(function (Contact $contact) {
            $contact->getSerialNumber(['customer_number', 'creditor_number']);
        });

        static::saving(function (Contact $contact) {
            // reset to original
            if ($contact->wasChanged(['customer_number', 'creditor_number'])) {
                $contact->customer_number = $contact->getOriginal('customer_number');
                $contact->creditor_number = $contact->getOriginal('creditor_number');
            }

            if (! $contact->exists && ! $contact->order_number) {
                $contact->getSerialNumber(['customer_number', 'creditor_number']);
            }
        });

        static::deleting(function (Contact $contact) {
            $contact->addresses()->delete();
        });
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function bankConnections(): HasMany
    {
        return $this->hasMany(BankConnection::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function sepaMandates(): HasMany
    {
        return $this->hasMany(SepaMandate::class);
    }

    public function serialNumbers(): HasManyThrough
    {
        return $this->hasManyThrough(SerialNumber::class, Address::class);
    }

    /**
     * Get the data to broadcast for the model.
     */
    public function broadcastWith(string $event): array
    {
        return match ($event) {
            'deleted' => ['model' => $this],
            default => ['model' => $this->load('addresses')]
        };
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

    public function getLabel(): ?string
    {
        return $this->customer_number;
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
        return $this->getFirstMediaUrl('images') ?: self::icon()->getUrl();
    }
}
