<?php

namespace FluxErp\Models;

use FluxErp\Contracts\OffersPrinting;
use FluxErp\Contracts\Targetable;
use FluxErp\Enums\CommunicationTypeEnum;
use FluxErp\Models\Pivots\Communicatable;
use FluxErp\Support\Scout\ScoutCustomize;
use FluxErp\Traits\HasDefaultTargetableColumns;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasTags;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\Printable;
use FluxErp\Traits\Scout\Searchable;
use FluxErp\Traits\SoftDeletes;
use FluxErp\View\Printing\Communication\CommunicationView;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Meilisearch\Endpoints\Indexes;
use Spatie\MediaLibrary\HasMedia;

class Communication extends FluxModel implements HasMedia, OffersPrinting, Targetable
{
    use HasDefaultTargetableColumns, HasPackageFactory, HasTags, HasUserModification, HasUuid, InteractsWithMedia,
        LogsActivity, Printable, Searchable, SoftDeletes;

    public static function timeframeColumns(): array
    {
        return [
            'date',
            'started_at',
            'ended_at',
            'created_at',
            'updated_at',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Communication $message): void {
            if ($message->isDirty('text_body')) {
                $message->text_body = strip_tags($message->text_body ?? '');
            }

            if ($message->isDirty('html_body') && $message->isClean('text_body') && ! trim($message->text_body)) {
                $message->text_body = strip_tags($message->html_body ?? '');
            }

            if (! $message->date) {
                $message->date = now();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'from' => 'array',
            'to' => 'array',
            'cc' => 'array',
            'bcc' => 'array',
            'communication_type_enum' => CommunicationTypeEnum::class,
            'date' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'is_seen' => 'boolean',
        ];
    }

    public function addresses(): MorphToMany
    {
        return $this->morphedByMany(Address::class, 'communicatable', 'communicatable');
    }

    public function autoAssign(string $type, array|string $matchAgainst): void
    {
        $matchAgainst = Arr::wrap($matchAgainst);
        $typeColumn = match ($type) {
            'phone' => 'phone',
            'email' => 'email_primary',
            default => null,
        };

        if (is_null($typeColumn)) {
            throw new InvalidArgumentException('Invalid type: ' . $type);
        }

        if ($matchAgainst) {
            $addresses = resolve_static(Address::class, 'query')
                ->where(function (Builder $query) use ($matchAgainst, $typeColumn): void {
                    $query->whereIn($typeColumn, $matchAgainst)
                        ->orWhereHas('contactOptions', function (Builder $query) use ($matchAgainst): void {
                            $query->whereIn('value', $matchAgainst);
                        });
                })
                ->with('contact:id')
                ->get(['id', 'contact_id'])
                ->each(
                    fn (Address $address) => $address->communications()->attach($this->id)
                );

            $contacts = $addresses->pluck('contact')->unique();

            if ($contacts->isNotEmpty()) {
                $contacts->each(
                    fn (Contact $contact) => $contact->communications()->attach($this->id)
                );
            }
        }

        if (is_string($this->subject) && ! empty($this->subject) && $type === 'email') {
            resolve_static(
                Order::class,
                'search',
                [
                    'query' => $this->subject,
                    'callback' => function (Indexes $meilisearch, string $query, array $options) {
                        return $meilisearch->search(
                            $query,
                            $options + ['attributesToSearchOn' => ['invoice_number', 'order_number', 'commission']]
                        );
                    },
                ]
            )
                ->first()
                ?->communications()
                ->attach($this->id);
        }
    }

    public function communicatables(): HasMany
    {
        return $this->hasMany(Communicatable::class);
    }

    public function contacts(): MorphToMany
    {
        return $this->morphedByMany(Contact::class, 'communicatable', 'communicatable');
    }

    public function getPrintViews(): array
    {
        return [
            'communication' => CommunicationView::class,
        ];
    }

    public function mailAccount(): BelongsTo
    {
        return $this->belongsTo(MailAccount::class);
    }

    public function mailFolder(): BelongsTo
    {
        return $this->belongsTo(MailFolder::class);
    }

    public function orders(): MorphToMany
    {
        return $this->morphedByMany(Order::class, 'communicatable', 'communicatable');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('message')
            ->acceptsMimeTypes(['application/pdf'])
            ->useDisk('local')
            ->singleFile();

        $this->addMediaCollection('attachments')
            ->useDisk('local');
    }

    public function toSearchableArray(): array
    {
        return ScoutCustomize::make($this)
            ->except('html_body')
            ->toSearchableArray();
    }

    protected function bccMail(): Attribute
    {
        return Attribute::get(
            fn ($value) => array_column($this->bcc ?? [], 'mail') ?: $this->bcc ?: []
        );
    }

    protected function broadcastWithout(): array
    {
        // exclude the body from broadcasting as the payload might be too large
        return [
            'text_body',
            'html_body',
        ];
    }

    protected function ccMail(): Attribute
    {
        return Attribute::get(
            fn ($value) => array_column($this->cc ?? [], 'mail') ?: $this->cc ?: []
        );
    }

    protected function fromMail(): Attribute
    {
        return Attribute::get(
            fn ($value) => Str::between($this->from ?? '', '<', '>') ?: $this->from ?: null
        );
    }

    protected function mailAddresses(): Attribute
    {
        return Attribute::get(
            fn ($value) => array_unique(
                array_merge(
                    [$this->from_mail],
                    $this->to_mail ?? [],
                    $this->cc_mail ?? [],
                    $this->bcc_mail ?? [],
                )
            )
        );
    }

    protected function toMail(): Attribute
    {
        return Attribute::get(
            fn ($value) => array_column($this->to ?? [], 'mail') ?: $this->to ?: []
        );
    }
}
