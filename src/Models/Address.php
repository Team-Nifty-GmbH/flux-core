<?php

namespace FluxErp\Models;

use FluxErp\Traits\BroadcastsEvents;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasCalendarEvents;
use FluxErp\Traits\HasFrontendAttributes;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\Lockable;
use FluxErp\Traits\Notifiable;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Casts\Attribute;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Searchable;
use Spatie\Permission\Traits\HasRoles;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Address extends Authenticatable implements HasLocalePreference, InteractsWithDataTables
{
    use BroadcastsEvents, Commentable, Filterable, HasAdditionalColumns, HasApiTokens, HasCalendarEvents, HasPackageFactory,
        HasFrontendAttributes, HasRoles, HasUserModification, HasUuid, Lockable, Notifiable, Searchable, SoftDeletes;

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
        'uuid',
    ];

    protected string $detailRouteName = 'contacts.id?';

    public static string $iconName = 'user';

    protected function loginPassword(): Attribute
    {
        return Attribute::set(
            fn ($value) => Hash::make($value)
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

    protected function name(): Attribute
    {
        return Attribute::get(
            fn () => $this->getLabel()
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
        return trim(
            ($this->company ?? '') . ' ' .
            ($this->firstname ?? '') . ' ' .
            ($this->lastname ?? '')
        );
    }

    public function getDescription(): ?string
    {
        return trim(
            ($this->company ?? '') . ' ' .
            ($this->firstname ?? '') . ' ' .
            ($this->lastname ?? '') . ' ' .
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
}
