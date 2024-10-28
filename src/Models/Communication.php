<?php

namespace FluxErp\Models;

use FluxErp\Contracts\OffersPrinting;
use FluxErp\Enums\CommunicationTypeEnum;
use FluxErp\Models\Pivots\Communicatable;
use FluxErp\Traits\HasPackageFactory;
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
use Meilisearch\Endpoints\Indexes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Tags\HasTags;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;

class Communication extends FluxModel implements HasMedia, OffersPrinting
{
    use BroadcastsEvents, HasPackageFactory, HasTags, HasUserModification, HasUuid, InteractsWithMedia, LogsActivity,
        Printable, Searchable, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    protected static function booted(): void
    {
        static::saving(function (Communication $message) {
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

    protected function broadcastWithout(): array
    {
        // exclude the body from broadcasting as the payload might be too large
        return [
            'text_body',
            'html_body',
        ];
    }

    protected function fromMail(): Attribute
    {
        return Attribute::get(
            fn ($value) => Str::between($this->from ?? '', '<', '>') ?: $this->from ?: null
        );
    }

    protected function toMail(): Attribute
    {
        return Attribute::get(
            fn ($value) => array_column($this->to ?? [], 'mail') ?: $this->to ?: []
        );
    }

    protected function ccMail(): Attribute
    {
        return Attribute::get(
            fn ($value) => array_column($this->cc ?? [], 'mail') ?: $this->cc ?: []
        );
    }

    protected function bccMail(): Attribute
    {
        return Attribute::get(
            fn ($value) => array_column($this->bcc ?? [], 'mail') ?: $this->bcc ?: []
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

    public function addresses(): MorphToMany
    {
        return $this->morphedByMany(Address::class, 'communicatable', 'communicatable');
    }

    public function communicatables(): HasMany
    {
        return $this->hasMany(Communicatable::class);
    }

    public function contacts(): MorphToMany
    {
        return $this->morphedByMany(Contact::class, 'communicatable', 'communicatable');
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

    public function toSearchableArray(): array
    {
        $array = $this->toArray();
        unset($array['html_body']);

        return $array;
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

    public function getPrintViews(): array
    {
        return [
            'communication' => CommunicationView::class,
        ];
    }

    public function autoAssign(string $type, array|string $matchAgainst): void
    {
        $matchAgainst = Arr::wrap($matchAgainst);
        $typeColumn = match($type) {
            'email' => 'email_primary',
            default => 'phone',
        };

        if ($matchAgainst) {
            $addresses = resolve_static(Address::class, 'query')
                ->where(function (Builder $query) use ($matchAgainst, $typeColumn) {
                    $query->whereIn($typeColumn, $matchAgainst)
                        ->orWhereHas('contactOptions', function (Builder $query) use ($matchAgainst) {
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
}
