<?php

namespace FluxErp\Models;

use Exception;
use FluxErp\Mail\MagicLoginLink;
use FluxErp\Models\Pivots\PrinterUser;
use FluxErp\Models\Pivots\TargetUser;
use FluxErp\Models\Pivots\TaskUser;
use FluxErp\Models\Pivots\TenantUser;
use FluxErp\Models\Pivots\TicketUser;
use FluxErp\Traits\Model\Calendar\HasCalendars;
use FluxErp\Traits\Model\Calendar\HasCalendarUserSettings;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasCart;
use FluxErp\Traits\Model\HasFrontendAttributes;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasParentChildRelations;
use FluxErp\Traits\Model\HasPushSubscriptions;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\HasWidgets;
use FluxErp\Traits\Model\InteractsWithMedia;
use FluxErp\Traits\Model\InteractsWithPasskeys;
use FluxErp\Traits\Model\MonitorsQueue;
use FluxErp\Traits\Model\Notifiable;
use FluxErp\Traits\Model\SoftDeletes;
use FluxErp\Traits\Model\TwoFactorAuthentication;
use FluxErp\Traits\Scout\Searchable;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Laragear\TwoFactor\Contracts\TwoFactorAuthenticatable;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\Permission\Traits\HasRoles;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;
use TeamNiftyGmbH\DataTable\Traits\HasDatatableUserSettings;

class User extends FluxAuthenticatable implements HasLocalePreference, HasMedia, HasPasskeys, InteractsWithDataTables, TwoFactorAuthenticatable
{
    use Filterable, HasCalendars, HasCalendarUserSettings, HasCart, HasDatatableUserSettings, HasFrontendAttributes,
        HasPackageFactory, HasParentChildRelations, HasPushSubscriptions, HasRoles, HasUserModification, HasUuid,
        HasWidgets, InteractsWithMedia, InteractsWithPasskeys, MonitorsQueue, Notifiable, SoftDeletes,
        TwoFactorAuthentication;
    use Searchable {
        Searchable::scoutIndexSettings as baseScoutIndexSettings;
    }

    public static string $iconName = 'user';

    protected $guarded = [
        'id',
    ];

    protected $hidden = [
        'password',
    ];

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

    // Public static methods
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

    public static function scoutIndexSettings(): ?array
    {
        return static::baseScoutIndexSettings() ?? [
            'filterableAttributes' => [
                'is_active',
            ],
        ];
    }

    protected function casts(): array
    {
        return [
            'force_two_factor' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Relations
    /**
     * @return HasMany<AbsenceRequest, $this>
     */
    public function absenceRequests(): HasMany
    {
        return $this->hasMany(AbsenceRequest::class);
    }

    /**
     * @return MorphMany<Activity, $this>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'causer');
    }

    /**
     * @return HasMany<CommissionRate, $this>
     */
    public function commissionRates(): HasMany
    {
        return $this->hasMany(CommissionRate::class);
    }

    /**
     * @return HasMany<Commission, $this>
     */
    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    /**
     * @return BelongsTo<Contact, $this>
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * @return HasOne<Employee, $this>
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * @return MorphMany<Favorite, $this>
     */
    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'authenticatable');
    }

    /**
     * @return BelongsTo<Language, $this>
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * @return HasMany<Lead, $this>
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * @return BelongsToMany<MailAccount, $this>
     */
    public function mailAccounts(): BelongsToMany
    {
        return $this->belongsToMany(MailAccount::class, 'mail_account_user')
            ->withPivot('is_default');
    }

    /**
     * @return BelongsToMany<Printer, $this>
     */
    public function printers(): BelongsToMany
    {
        return $this->belongsToMany(Printer::class, 'printer_user')
            ->using(PrinterUser::class);
    }

    /**
     * @return HasMany<PrinterUser, $this>
     */
    public function printerUsers(): HasMany
    {
        return $this->hasMany(PrinterUser::class);
    }

    /**
     * @return BelongsToMany<Target, $this>
     */
    public function targets(): BelongsToMany
    {
        return $this->belongsToMany(Target::class, 'target_user')
            ->using(TargetUser::class);
    }

    /**
     * @return BelongsToMany<Task, $this>
     */
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_user')
            ->using(TaskUser::class);
    }

    /**
     * @return HasMany<Task, $this>
     */
    public function tasksResponsible(): HasMany
    {
        return $this->hasMany(Task::class, 'responsible_user_id');
    }

    /**
     * @return BelongsToMany<Ticket, $this>
     */
    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'ticket_user')
            ->using(TicketUser::class);
    }

    /**
     * @return BelongsToMany<Tenant, $this>
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user')
            ->using(TenantUser::class);
    }

    /**
     * @return HasMany<WorkTime, $this>
     */
    public function workTimes(): HasMany
    {
        return $this->hasMany(WorkTime::class);
    }

    // Public methods
    public function defaultMailAccount(): ?MailAccount
    {
        return $this->mailAccounts()
            ->wherePivot('is_default', true)
            ->first();
    }

    public function generateLoginLink(?string $intendedUrl = null): string
    {
        $plaintextToken = Str::uuid()->toString();
        $expires = now()->addMinutes(15);
        Cache::put('login_token_' . $plaintextToken,
            [
                'user_type' => $this->getMorphClass(),
                'user_id' => $this->getKey(),
                'guard' => 'web',
                'intended_url' => $intendedUrl ?? Session::get('url.intended', route('dashboard')),
            ],
            $expires
        );

        return URL::temporarySignedRoute(
            'login-link',
            $expires,
            [
                'token' => $plaintextToken,
            ]
        );
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

    /**
     * Get the preferred locale of the entity.
     */
    public function preferredLocale(): ?string
    {
        return $this->language?->language_code;
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
        Mail::to($this->email)->queue(MagicLoginLink::make($this->generateLoginLink()));
    }

    // Attributes
    protected function password(): Attribute
    {
        return Attribute::set(
            fn ($value) => Hash::info($value)['algoName'] !== 'bcrypt' ? Hash::make($value) : $value,
        );
    }
}
