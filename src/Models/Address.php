<?php

namespace FluxErp\Models;

use FluxErp\Enums\SalutationEnum;
use FluxErp\Mail\MagicLoginLink;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\Communicatable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasCart;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasFrontendAttributes;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\Lockable;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\MonitorsQueue;
use FluxErp\Traits\Notifiable;
use FluxErp\Traits\Scout\Searchable;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\UnauthorizedException;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Tags\HasTags;
use TeamNiftyGmbH\Calendar\Traits\HasCalendars;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;

class Address extends Authenticatable implements HasLocalePreference, InteractsWithDataTables
{
    use BroadcastsEvents, Commentable, Communicatable, Filterable, HasAdditionalColumns, HasApiTokens, HasCalendars,
        HasCart, HasClientAssignment, HasFrontendAttributes, HasPackageFactory, HasRoles, HasTags, HasUserModification,
        HasUuid, Lockable, LogsActivity, MonitorsQueue, Notifiable, Searchable, SoftDeletes;

    protected $hidden = [
        'password',
    ];

    protected $guarded = [
        'id',
    ];

    protected ?string $detailRouteName = 'contacts.id?';

    public static string $iconName = 'user';

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
                $this->street,
                trim($this->zip . ' ' . $this->city),
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

    public function serialNumbers(): HasMany
    {
        return $this->hasMany(SerialNumber::class);
    }

    public function settings(): MorphMany
    {
        return $this->morphMany(Setting::class, 'model');
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
}
