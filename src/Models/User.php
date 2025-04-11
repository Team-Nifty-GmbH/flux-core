<?php

namespace FluxErp\Models;

use Exception;
use FluxErp\Mail\MagicLoginLink;
use FluxErp\Models\Pivots\PrinterUser;
use FluxErp\Traits\CacheModelQueries;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasCalendars;
use FluxErp\Traits\HasCalendarUserSettings;
use FluxErp\Traits\HasCart;
use FluxErp\Traits\HasFrontendAttributes;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasParentChildRelations;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\HasWidgets;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\MonitorsQueue;
use FluxErp\Traits\Notifiable;
use FluxErp\Traits\Scout\Searchable;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\Permission\Traits\HasRoles;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;
use TeamNiftyGmbH\DataTable\Traits\HasDatatableUserSettings;

class User extends FluxAuthenticatable implements HasLocalePreference, HasMedia, InteractsWithDataTables
{
    use CacheModelQueries, Filterable, HasCalendars, HasCalendarUserSettings, HasCart, HasDatatableUserSettings,
        HasFrontendAttributes, HasPackageFactory, HasParentChildRelations, HasPushSubscriptions, HasRoles,
        HasUserModification, HasUuid, HasWidgets, InteractsWithMedia, MonitorsQueue, Notifiable, Searchable,
        SoftDeletes;

    public static string $iconName = 'user';

    protected $guarded = [
        'id',
    ];

    protected $hidden = [
        'password',
    ];

    public static function guardNames(): array
    {
        return [
            'web',
            'sanctum',
        ];
    }

    public static function hasPermission(): bool
    {
        return false;
    }

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if ($user->isDirty('lastname') || $user->isDirty('firstname')) {
                $user->name = trim($user->firstname . ' ' . $user->lastname);
            }

            if ($user->isDirty('iban')) {
                $user->iban = str_replace(' ', '', strtoupper($user->iban));
            }
        });

        static::saved(function (User $user): void {
            Cache::forget('morph_to:' . $user->getMorphClass() . ':' . $user->id);
        });
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'causer');
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_user');
    }

    public function commissionRates(): HasMany
    {
        return $this->hasMany(CommissionRate::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'authenticatable');
    }

    /**
     * @throws Exception
     */
    public function getAvatarUrl(): ?string
    {
        return $this->getFirstMediaUrl('avatar', 'thumb') ?: static::icon()->getUrl();
    }

    public function getDescription(): ?string
    {
        return $this->email;
    }

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getUrl(): ?string
    {
        return null;
    }

    public function guardName(): array
    {
        return static::guardNames();
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

    /**
     * Get the preferred locale of the entity.
     */
    public function preferredLocale(): ?string
    {
        return $this->language?->language_code;
    }

    public function printers(): BelongsToMany
    {
        return $this->belongsToMany(Printer::class, 'printer_user')
            ->using(PrinterUser::class);
    }

    public function printerUsers(): HasMany
    {
        return $this->hasMany(PrinterUser::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->acceptsFile(function (File $file) {
                return str_starts_with($file->mimeType, 'image/');
            })
            ->useFallbackUrl(static::icon()->getUrl())
            ->useDisk('public')
            ->singleFile();
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

        Mail::to($this->email)->queue(MagicLoginLink::make($plaintext, $expires));
    }

    public function settings(): MorphMany
    {
        return $this->morphMany(Setting::class, 'model');
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_user');
    }

    public function tasksResponsible(): HasMany
    {
        return $this->hasMany(Task::class, 'responsible_user_id');
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'ticket_user');
    }

    public function workTimes(): HasMany
    {
        return $this->hasMany(WorkTime::class);
    }

    protected function password(): Attribute
    {
        return Attribute::set(
            fn ($value) => Hash::info($value)['algoName'] !== 'bcrypt' ? Hash::make($value) : $value,
        );
    }
}
