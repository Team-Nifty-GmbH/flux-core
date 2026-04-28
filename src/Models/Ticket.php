<?php

namespace FluxErp\Models;

use Exception;
use FluxErp\Casts\Money;
use FluxErp\Contracts\IsSubscribable;
use FluxErp\Models\Pivots\TicketUser;
use FluxErp\States\Ticket\TicketState;
use FluxErp\Support\Scout\ScoutCustomize;
use FluxErp\Traits\HasStates;
use FluxErp\Traits\Model\Commentable;
use FluxErp\Traits\Model\Communicatable;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasFrontendAttributes;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasSerialNumberRange;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\InteractsWithMedia;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use FluxErp\Traits\Model\Trackable;
use FluxErp\Traits\Scout\Searchable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Ticket extends FluxModel implements HasMedia, InteractsWithDataTables, IsSubscribable
{
    use Commentable, Communicatable, Filterable, HasFrontendAttributes, HasPackageFactory, HasSerialNumberRange,
        HasStates, HasUserModification, HasUuid, InteractsWithMedia, LogsActivity, SoftDeletes, Trackable;
    use Searchable {
        Searchable::scoutIndexSettings as baseScoutIndexSettings;
    }

    public static string $iconName = 'chat-bubble-left-right';

    protected ?string $detailRouteName = 'tickets.id';

    protected static function booted(): void
    {
        static::saving(function (Ticket $ticket): void {
            if ($ticket->isDirty('state')) {
                $ticketStateClass = resolve_static(TicketState::class, 'class');

                if (
                    $ticket->state instanceof $ticketStateClass
                    && $ticket->state::$isEndState
                ) {
                    $ticket->resolved_at ??= now();
                    $ticket->resolved_by ??= auth()->user()
                        ? auth()->user()->getMorphClass() . ':' . auth()->id()
                        : null;
                } else {
                    $ticket->resolved_at = null;
                    $ticket->resolved_by = null;
                }
            }
        });
    }

    // Public static methods
    public static function scoutIndexSettings(): ?array
    {
        return static::baseScoutIndexSettings() ?? [
            'filterableAttributes' => [
                'authenticatable_type',
                'authenticatable_id',
                'state',
            ],
        ];
    }

    protected function casts(): array
    {
        return [
            'state' => TicketState::class,
            'total_cost' => Money::class,
            'resolved_at' => 'datetime',
        ];
    }

    // Relations
    public function authenticatable(): MorphTo
    {
        return $this->morphTo('authenticatable');
    }

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ticket_user')
            ->using(TicketUser::class);
    }

    // Public methods
    public function costColumn(): string
    {
        return 'total_cost';
    }

    public function detailRouteParams(): array
    {
        return [
            'id' => $this->id,
        ];
    }

    /**
     * @throws Exception
     */
    public function getAvatarUrl(): ?string
    {
        return $this->getFirstMediaUrl('images') ?: static::icon()->getUrl();
    }

    public function getContactId(): ?int
    {
        return $this->authenticatable_type === morph_alias(Address::class)
            ? resolve_static(Address::class, 'query')
                ->whereKey($this->authenticatable_id)
                ->value('contact_id')
            : null;
    }

    public function getDescription(): ?string
    {
        return Str::limit($this->description, 200);
    }

    public function getLabel(): ?string
    {
        return $this->title . ' ' . $this->ticket_number;
    }

    public function getUrl(): ?string
    {
        return $this->detailRoute();
    }

    public function toSearchableArray(): array
    {
        return ScoutCustomize::make($this)
            ->with(['authenticatable', 'ticketType:id,name'])
            ->toSearchableArray();
    }
}
