<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\ContactDiscount;
use FluxErp\Models\Pivots\ContactDiscountGroup;
use FluxErp\Traits\Categorizable;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\Communicatable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasFrontendAttributes;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasSerialNumberRange;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\Lockable;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;

class Contact extends Model implements HasMedia, InteractsWithDataTables
{
    use BroadcastsEvents, Categorizable, Commentable, Communicatable, Filterable, HasAdditionalColumns,
        HasClientAssignment, HasFrontendAttributes, HasPackageFactory, HasSerialNumberRange, HasUserModification,
        HasUuid, InteractsWithMedia, Lockable, SoftDeletes;

    protected $casts = [
        'uuid' => 'string',
        'has_sensitive_reminder' => 'boolean',
        'has_delivery_lock' => 'boolean',
    ];

    protected $guarded = [
        'id',
    ];

    public static string $iconName = 'users';

    public static function booted(): void
    {
        static::saving(function (Contact $contact) {
            // reset to original
            if ($contact->wasChanged(['customer_number', 'creditor_number', 'debtor_number'])) {
                $contact->customer_number = $contact->getOriginal('customer_number');
                $contact->creditor_number = $contact->getOriginal('creditor_number');
                $contact->creditor_number = $contact->getOriginal('debtor_number');
            }

            if (! $contact->exists) {
                $contact->getSerialNumber(['customer_number', 'creditor_number', 'debtor_number']);
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

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function contactBankConnections(): HasMany
    {
        return $this->hasMany(ContactBankConnection::class);
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'delivery_address_id');
    }

    public function discountGroups(): BelongsToMany
    {
        return $this->belongsToMany(DiscountGroup::class, 'contact_discount_group')
            ->using(ContactDiscountGroup::class);
    }

    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'contact_discount')->using(ContactDiscount::class);
    }

    public function invoiceAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'invoice_address_id');
    }

    public function ledgerAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class);
    }

    public function mainAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'main_address_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_supplier');
    }

    public function sepaMandates(): HasMany
    {
        return $this->hasMany(SepaMandate::class);
    }

    public function serialNumbers(): HasManyThrough
    {
        return $this->hasManyThrough(SerialNumber::class, Address::class);
    }

    public function vatRate(): BelongsTo
    {
        return $this->belongsTo(VatRate::class);
    }

    public function getAllDiscountsQuery(): Builder
    {
        $directDiscountsQuery = $this->discounts()
            ->select('discounts.*')
            ->where(function ($query) {
                $query->whereNull('from')
                    ->orWhere('from', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('till')
                    ->orWhere('till', '>=', now());
            })
            ->getQuery();

        $discountsThroughGroupsQuery = $this->discountGroups()
            ->join('discount_discount_group', 'discount_groups.id', '=', 'discount_discount_group.discount_group_id')
            ->join('discounts', 'discounts.id', '=', 'discount_discount_group.discount_id')
            ->select('discounts.*')
            ->where('discount_groups.is_active', true)
            ->where(function ($query) {
                $query->whereNull('from')
                    ->orWhere('from', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('till')
                    ->orWhere('till', '>=', now());
            })
            ->getQuery();

        return app(Discount::class)->newModelQuery()
            ->fromSub($directDiscountsQuery->union($discountsThroughGroupsQuery), 'union_sub');
    }

    public function getAllDiscounts(): Collection
    {
        return $this->getAllDiscountsQuery()
            ->get()
            ->sortByDesc('discount')
            ->unique(fn ($item) => $item->model_id . $item->model_type . $item->is_percentage)
            ->values();
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
        return $this->getFirstMediaUrl('avatar', 'thumb') ?: self::icon()->getUrl();
    }
}
