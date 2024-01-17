<?php

namespace FluxErp\Models;

use FluxErp\Mail\MagicLoginLink;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasFrontendAttributes;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\HasWidgets;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\Notifiable;
use FluxErp\Traits\SoftDeletes;
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
use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\Permission\Traits\HasRoles;
use TeamNiftyGmbH\Calendar\Traits\HasCalendars;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;
use TeamNiftyGmbH\DataTable\Traits\HasDatatableUserSettings;

class User extends Authenticatable implements HasLocalePreference, HasMedia, InteractsWithDataTables
{
    use BroadcastsEvents, Commentable, Filterable, HasApiTokens, HasCalendars, HasDatatableUserSettings,
        HasFrontendAttributes, HasPackageFactory, HasPushSubscriptions, HasRoles, HasUuid, HasWidgets,
        InteractsWithMedia, Notifiable, Searchable, SoftDeletes;

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'uuid' => 'string',
        'is_active' => 'boolean',
    ];

    protected $guarded = [
        'id',
    ];

    public static string $iconName = 'user';

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->isDirty('lastname') || $user->isDirty('firstname')) {
                $user->name = trim($user->firstname . ' ' . $user->lastname);
            }
        });
    }

    protected function password(): Attribute
    {
        return Attribute::set(
            fn ($value) => Hash::info($value)['algoName'] !== 'bcrypt' ? Hash::make($value) : $value,
        );
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'causer');
    }

    public function children(): HasMany
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function commissionRates(): HasMany
    {
        return $this->hasMany(CommissionRate::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'authenticatable');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function locks(): MorphMany
    {
        return $this->morphMany(Lock::class, 'authenticatable');
    }

    public function mailAccounts(): BelongsToMany
    {
        return $this->belongsToMany(MailAccount::class, 'mail_account_user');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function settings(): MorphMany
    {
        return $this->morphMany(Setting::class, 'model');
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_user');
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'ticket_user');
    }

    public function workTimes(): HasMany
    {
        return $this->hasMany(WorkTime::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->acceptsFile(function (File $file) {
                return str_starts_with($file->mimeType, 'image/');
            })
            ->useFallbackUrl(self::icon()->getUrl())
            ->useDisk('public')
            ->singleFile();
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
        return $this->email;
    }

    public function getUrl(): ?string
    {
        return null;
    }

    /**
     * @throws \Exception
     */
    public function getAvatarUrl(): ?string
    {
        return $this->getFirstMediaUrl('avatar', 'thumb') ?: self::icon()->getUrl();
    }

    public function sendLoginLink(): void
    {
        $plaintext = Str::uuid()->toString();
        $expires = now()->addMinutes(15);
        Cache::put('login_token_' . $plaintext,
            [
                'user' => $this,
                'guard' => 'web',
                'intended_url' => Session::get('url.intended', route('dashboard')),
            ],
            $expires
        );

        Mail::to($this->email)->queue(new MagicLoginLink($plaintext, $expires));
    }
}
