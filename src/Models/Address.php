<?php

namespace FluxErp\Models;

use Carbon\Carbon;
use FluxErp\Contracts\Calendarable;
use FluxErp\Contracts\OffersPrinting;
use FluxErp\Enums\SalutationEnum;
use FluxErp\Mail\MagicLoginLink;
use FluxErp\Models\Pivots\AddressAddressTypeOrder;
use FluxErp\States\Address\AdvertisingState;
use FluxErp\Support\Collection\AddressCollection;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\Communicatable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasCalendars;
use FluxErp\Traits\HasCart;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasFrontendAttributes;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasTags;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\Lockable;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\MonitorsQueue;
use FluxErp\Traits\Notifiable;
use FluxErp\Traits\Printable;
use FluxErp\Traits\Scout\Searchable;
use FluxErp\Traits\SoftDeletes;
use FluxErp\View\Printing\Address\AddressLabel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\UnauthorizedException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\ModelStates\HasStates;
use Spatie\Permission\Traits\HasRoles;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Address extends FluxAuthenticatable implements Calendarable, HasLocalePreference, HasMedia, InteractsWithDataTables, OffersPrinting
{
    use Commentable, Communicatable, Filterable, HasAdditionalColumns, HasCalendars, HasCart, HasClientAssignment,
        HasFrontendAttributes, HasPackageFactory, HasRoles, HasStates, HasTags, HasUserModification, HasUuid,
        InteractsWithMedia, Lockable, LogsActivity, MonitorsQueue, Notifiable, Printable, Searchable, SoftDeletes;

    protected $hidden = [
        'password',
    ];

    protected $guarded = [
        'id',
    ];

    protected ?string $detailRouteName = 'contacts.id?';

    public static string $iconName = 'user';

    public static function findAddressByEmail(string $email): ?Address
    {
        $address = null;
        if ($email) {
            $address = resolve_static(Address::class, 'query')
                ->with('contact')
                ->where('email', $email)
                ->orWhere('email_primary', $email)
                ->first();

            if (! $address) {
                $address = resolve_static(ContactOption::class, 'query')
                    ->with(['contact', 'address'])
                    ->where('value', $email)
                    ->first()
                    ?->address;
            }

            if (! $address) {
                $address = resolve_static(Address::class, 'query')
                    ->with('contact')
                    ->where('url', 'like', '%' . Str::after($email, '@'))
                    ->first();
            }
        }

        return $address;
    }

    protected static function booted(): void
    {
        static::saving(function (Address $address) {
            if ($address->isDirty('lastname')
                || $address->isDirty('firstname')
                || $address->isDirty('company')
            ) {
                $name = [
                    $address->company,
                    trim($address->firstname . ' ' . $address->lastname),
                ];

                $address->name = implode(', ', array_filter($name)) ?: null;
            }
        });

        static::saved(function (Address $address) {
            Cache::forget('morph_to:' . $address->getMorphClass() . ':' . $address->id);

            $contactUpdates = [];
            $addressesUpdates = [];

            if ($address->isDirty('is_main_address') && $address->is_main_address) {
                $contactUpdates += [
                    'main_address_id' => $address->id,
                ];

                $addressesUpdates += [
                    'is_main_address' => false,
                ];
            }

            if ($address->isDirty('is_invoice_address') && $address->is_invoice_address) {
                $contactUpdates += [
                    'invoice_address_id' => $address->id,
                ];

                $addressesUpdates += [
                    'is_invoice_address' => false,
                ];
            }

            if ($address->isDirty('is_delivery_address') && $address->is_delivery_address) {
                $contactUpdates += [
                    'delivery_address_id' => $address->id,
                ];

                $addressesUpdates += [
                    'is_delivery_address' => false,
                ];
            }

            if ($contactUpdates) {
                resolve_static(Contact::class, 'query')
                    ->whereKey($address->contact_id)
                    ->update($contactUpdates);

                resolve_static(Address::class, 'query')
                    ->where('contact_id', $address->contact_id)
                    ->where('id', '!=', $address->id)
                    ->update($addressesUpdates);
            }
        });

        static::deleted(function (Address $address) {
            $contactUpdates = [];
            $addressesUpdates = [];
            $mainAddress = resolve_static(Address::class, 'query')
                ->where('contact_id', $address->contact_id)
                ->where('is_main_address', true)
                ->first();

            if ($address->is_invoice_address) {
                $contactUpdates += [
                    'invoice_address_id' => $mainAddress->id,
                ];

                $addressesUpdates += [
                    'is_invoice_address' => true,
                ];
            }

            if ($address->is_delivery_address) {
                $contactUpdates += [
                    'delivery_address_id' => $mainAddress->id,
                ];

                $addressesUpdates += [
                    'is_delivery_address' => true,
                ];
            }

            if ($contactUpdates) {
                resolve_static(Contact::class, 'query')
                    ->whereKey($address->contact_id)
                    ->update($contactUpdates);

                $mainAddress->update($addressesUpdates);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'advertising_state' => AdvertisingState::class,
            'has_formal_salutation' => 'boolean',
            'is_main_address' => 'boolean',
            'is_invoice_address' => 'boolean',
            'is_dark_mode' => 'boolean',
            'is_delivery_address' => 'boolean',
            'is_active' => 'boolean',
            'can_login' => 'boolean',
        ];
    }

    public function routeNotificationForMail(): ?string
    {
        return $this->email ?? $this->email_primary;
    }

    protected function password(): Attribute
    {
        return Attribute::set(
            fn ($value) => Hash::info($value)['algoName'] !== 'bcrypt' ? Hash::make($value) : $value,
        );
    }

    protected function postalAddress(): Attribute
    {
        return Attribute::get(
            fn () => array_filter([
                $this->company,
                trim($this->firstname . ' ' . $this->lastname),
                $this->addition,
                $this->street,
                trim($this->country?->iso_alpha2 . ' ' . $this->zip . ' ' . $this->city),
                $this->country?->name,
            ])
        );
    }

    public function salutation(): ?string
    {
        try {
            $enum = SalutationEnum::from($this->salutation ?? '');
        } catch (\Throwable) {
            $enum = SalutationEnum::NO_SALUTATION;
        }

        return $enum->salutation($this);
    }

    public function addressTypeOrders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'address_address_type_order')
            ->using(AddressAddressTypeOrder::class)
            ->withPivot('address_type_id');
    }

    public function addressTypes(): BelongsToMany
    {
        return $this->belongsToMany(AddressType::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function contactOptions(): HasMany
    {
        return $this->hasMany(ContactOption::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'address_address_type_order')
            ->withPivot('address_type_id');
    }

    public function ordersDeliveryAddress(): HasMany
    {
        return $this->hasMany(Order::class, 'address_delivery_id');
    }

    public function ordersInvoiceAddress(): HasMany
    {
        return $this->hasMany(Order::class, 'address_invoice_id');
    }

    public function priceList(): HasOneThrough
    {
        return $this->hasOneThrough(
            PriceList::class,
            Contact::class,
            'id',
            'id',
            'contact_id',
            'price_list_id'
        );
    }

    public function projectTasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function serialNumbers(): BelongsToMany
    {
        return $this->belongsToMany(SerialNumber::class, 'address_serial_number');
    }

    public function settings(): MorphMany
    {
        return $this->morphMany(Setting::class, 'model');
    }

    public function newCollection(array $models = []): Collection
    {
        return app(AddressCollection::class, ['items' => $models]);
    }

    /**
     * Get the channels that model events should broadcast on.
     *
     * @param  string  $event
     */
    public function broadcastOn($event): array
    {
        return [
            new PrivateChannel($this->broadcastChannel()),
            new PrivateChannel((app(Contact::class))->broadcastChannel() . $this->contact_id),
        ];
    }

    public function detailRouteParams(): array
    {
        return [
            'id' => $this->contact_id,
            'address' => $this->id,
        ];
    }

    /**
     * Get the preferred locale of the entity.
     */
    public function preferredLocale(): ?string
    {
        return $this->language?->language_code;
    }

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return implode(', ', $this->postal_address);
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
        return $this->contact?->getAvatarUrl();
    }

    public function createLoginToken(): array
    {
        if (! $this->can_login || ! $this->is_active) {
            throw new UnauthorizedException('Address cannot login');
        }

        $plaintext = Str::uuid()->toString();
        $expires = now()->addMinutes(15);
        Cache::put('login_token_' . $plaintext,
            [
                'user' => $this,
                'guard' => 'address',
                'intended_url' => Session::get('url.intended', route('portal.dashboard')),
            ],
            $expires
        );
        URL::forceRootUrl(config('flux.portal_domain'));

        return [
            'token' => $plaintext,
            'expires' => $expires,
            'url' => URL::temporarySignedRoute(
                'login-link',
                $expires,
                [
                    'token' => $plaintext,
                ]
            ),
        ];
    }

    public function sendLoginLink(): void
    {
        try {
            $login = $this->createLoginToken();
        } catch (UnauthorizedException) {
            return;
        }

        // dont queue mail as the address isnt used as auth in the regular app url
        Mail::to($this->email)->send(MagicLoginLink::make($login['token'], $login['expires']));
    }

    public function getPrintViews(): array
    {
        return [
            'address-label' => AddressLabel::class,
        ];
    }

    public static function toCalendar(): array
    {
        return [
            'id' => Str::of(static::class)->replace('\\', '.'),
            'modelType' => morph_alias(static::class),
            'name' => __('Birthdays'),
            'color' => '#dd2c2c',
            'resourceEditable' => false,
            'hasRepeatableEvents' => false,
            'isPublic' => false,
            'isShared' => false,
            'permission' => 'owner',
            'group' => 'other',
            'isVirtual' => true,
        ];
    }

    public function toCalendarEvent(?array $info = null): array
    {
        $currentBirthday = $this->date_of_birth->setYear(Carbon::parse(data_get($info, 'start'))->year);
        $age = $currentBirthday->diffInYears($this->date_of_birth);
        $name = <<<HTML
            <i class="ph ph-gift"></i>
            <span>$this->name ($age)</span>
        HTML;

        return [
            'id' => $this->id,
            'calendar_type' => $this->getMorphClass(),
            'title' => $name,
            'start' => $currentBirthday->toDateString(),
            'end' => $currentBirthday->toDateString(),
            'invited' => [],
            'allDay' => true,
            'is_editable' => false,
            'is_invited' => false,
            'is_public' => false,
        ];
    }

    public function scopeInTimeframe(
        Builder $builder,
        Carbon|string|null $start,
        Carbon|string|null $end,
        ?array $info = null
    ): void {
        $start = $start ? Carbon::parse($start) : null;
        $end = $end ? Carbon::parse($end) : null;

        $builder->where(function (Builder $query) use ($start, $end): void {
            if ($start && $end && $start->greaterThan($end)) {
                $query->where(function (Builder $query) use ($start): void {
                    $query->whereMonth('date_of_birth', '=', $start->month)
                        ->whereDay('date_of_birth', '>=', $start->day)
                        ->orWhere(function (Builder $q2): void {
                            $q2->whereMonth('date_of_birth', '=', 12);
                        });
                })->orWhere(function (Builder $q) use ($end): void {
                    $q->whereMonth('date_of_birth', '=', $end->month)
                        ->whereDay('date_of_birth', '<=', $end->day);
                });
            } else {
                // Normal range (no wrapping)
                $query->where(function (Builder $query) use ($start, $end): void {
                    if ($start) {
                        $query->whereMonth('date_of_birth', '>', $start->month)
                            ->orWhere(function (Builder $query) use ($start): void {
                                $query->whereMonth('date_of_birth', '=', $start->month)
                                    ->whereDay('date_of_birth', '>=', $start->day);
                            });
                    }
                    if ($end) {
                        $query->whereMonth('date_of_birth', '<', $end->month)
                            ->orWhere(function (Builder $query) use ($end): void {
                                $query->whereMonth('date_of_birth', '=', $end->month)
                                    ->whereDay('date_of_birth', '<=', $end->day);
                            });
                    }
                });
            }
        });
    }

    public static function fromCalendarEvent(array $event): Model
    {
        $currentAddress = static::query()->whereKey(data_get($event, 'id'))->first();

        $address = new static();
        $address->forceFill([
            'id' => data_get($event, 'id'),
            'date_of_birth' => Carbon::parse(data_get($event, 'start'))->setYear($currentAddress->date_of_birth->year),
        ]);

        return $address;
    }
}
