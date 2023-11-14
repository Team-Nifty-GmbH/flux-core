<?php

namespace FluxErp\Models;

use FluxErp\Mail\MagicLoginLink;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasCalendarEvents;
use FluxErp\Traits\HasFrontendAttributes;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\Lockable;
use FluxErp\Traits\Notifiable;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Searchable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Tags\HasTags;
use TeamNiftyGmbH\Calendar\Traits\HasCalendars;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;

class Address extends Authenticatable implements HasLocalePreference, InteractsWithDataTables
{
    use BroadcastsEvents, Commentable, Filterable, HasAdditionalColumns, HasApiTokens, HasCalendarEvents, HasCalendars,
        HasFrontendAttributes, HasPackageFactory, HasRoles, HasTags, HasUserModification, HasUuid,
        Notifiable, Searchable, SoftDeletes;

    protected $hidden = [
        'login_password',
    ];

    protected $casts = [
        'uuid' => 'string',
        'is_main_address' => 'boolean',
        'is_active' => 'boolean',
        'can_login' => 'boolean',
    ];

    protected $guarded = [
        'id',
    ];

    protected string $detailRouteName = 'contacts.id?';

    public static string $iconName = 'user';

    protected static function booted(): void
    {
        static::saving(function (Address $address) {
            if ($address->isDirty('lastname') || $address->isDirty('firstname') || $address->isDirty('company')) {
                $name = [
                    $address->company,
                    trim($address->firstname . ' ' . $address->lastname),
                ];

                $address->name = implode(', ', array_filter($name)) ?: null;
            }
        });
    }

    public function getAuthPassword()
    {
        return $this->login_password;
    }

    protected function loginPassword(): Attribute
    {
        return Attribute::set(
            fn ($value) => Hash::info($value)['algoName'] !== 'bcrypt' ? Hash::make($value) : $value,
        );
    }

    protected function email(): Attribute
    {
        return Attribute::get(
            fn () => $this->contactOptions()
                ->where('type', 'email')
                ->orderBy('is_primary', 'desc')
                ->first()
                ->value ?? null
        );
    }

    protected function phone(): Attribute
    {
        return Attribute::get(
            fn () => $this->contactOptions()
                ->where('type', 'phone')
                ->orderBy('is_primary', 'desc')
                ->first()
                ->value ?? null
        );
    }

    protected function website(): Attribute
    {
        return Attribute::get(
            fn () => $this->contactOptions()
                ->where('type', 'website')
                ->orderBy('is_primary', 'desc')
                ->first()
                ->value ?? null
        );
    }

    public function addressTypes(): BelongsToMany
    {
        return $this->belongsToMany(AddressType::class);
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

    public function serialNumbers(): HasMany
    {
        return $this->hasMany(SerialNumber::class);
    }

    public function settings(): MorphMany
    {
        return $this->morphMany(Setting::class, 'model');
    }

    public function projectTasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class);
    }

    /**
     * Get the channels that model events should broadcast on.
     *
     * @param  string  $event
     */
    public function broadcastOn($event): PrivateChannel
    {
        return new PrivateChannel((new Contact())->broadcastChannel() . '.' . $this->contact_id);
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
        return trim(
            $this->name . ' ' .
            ($this->street ?? '') . ' ' .
            ($this->zip ?? '') . ' ' .
            ($this->city ?? '') . ' ' .
            ($this->country ?? '')
        ) ?: '';
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
        return $this->contact?->getFirstMediaUrl('avatar') ?: self::icon()->getUrl();
    }

    public function sendLoginLink(): void
    {
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

        Mail::to($this->login_name)->queue(new MagicLoginLink($plaintext, $expires));
    }
}
