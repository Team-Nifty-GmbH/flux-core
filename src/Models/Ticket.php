<?php

namespace FluxErp\Models;

use FluxErp\Casts\Money;
use FluxErp\States\Ticket\TicketState;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\Communicatable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasCustomEvents;
use FluxErp\Traits\HasFrontendAttributes;
use FluxErp\Traits\HasNotificationSubscriptions;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasRelatedModel;
use FluxErp\Traits\HasSerialNumberRange;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\Scout\Searchable;
use FluxErp\Traits\SoftDeletes;
use FluxErp\Traits\Trackable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\ModelStates\HasStates;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Ticket extends FluxModel implements HasMedia, InteractsWithDataTables
{
    use Commentable, Communicatable, Filterable, HasAdditionalColumns, HasCustomEvents, HasFrontendAttributes,
        HasNotificationSubscriptions, HasPackageFactory, HasRelatedModel, HasSerialNumberRange, HasStates,
        HasUserModification, HasUuid, InteractsWithMedia, LogsActivity, Searchable, SoftDeletes, Trackable;

    protected ?string $detailRouteName = 'tickets.id';

    protected array $relatedCustomEvents = [
        'ticketType',
    ];

    public static string $iconName = 'chat-bubble-left-right';

    protected function casts(): array
    {
        return [
            'state' => TicketState::class,
            'total_cost' => Money::class,
        ];
    }

    public function getContactId(): ?int
    {
        return $this->authenticatable_type === morph_alias(Address::class)
            ? resolve_static(Address::class, 'query')
                ->whereKey($this->authenticatable_id)
                ->value('contact_id')
            : null;
    }

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
        return $this->belongsToMany(User::class, 'ticket_user');
    }

    public function detailRouteParams(): array
    {
        return [
            'id' => $this->id,
        ];
    }

    public function getLabel(): ?string
    {
        return $this->title . ' ' . $this->ticket_number;
    }

    public function getDescription(): ?string
    {
        return Str::limit($this->description, 200);
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
        return $this->getFirstMediaUrl('images') ?: static::icon()->getUrl();
    }

    public function getPortalDetailRoute(): string
    {
        return route('portal.tickets.id', ['id' => $this->id]);
    }

    public function costColumn(): string
    {
        return 'total_cost';
    }
}
