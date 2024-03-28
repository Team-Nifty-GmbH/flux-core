<?php

namespace FluxErp\Models;

use FluxErp\States\Ticket\TicketState;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasCustomEvents;
use FluxErp\Traits\HasFrontendAttributes;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasRelatedModel;
use FluxErp\Traits\HasSerialNumberRange;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\SoftDeletes;
use FluxErp\Traits\Trackable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\ModelStates\HasStates;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;

class Ticket extends Model implements HasMedia, InteractsWithDataTables
{
    use BroadcastsEvents, Commentable, Filterable, HasAdditionalColumns, HasCustomEvents, HasFrontendAttributes,
        HasPackageFactory, HasRelatedModel, HasSerialNumberRange, HasStates, HasUserModification, HasUuid,
        InteractsWithMedia, Searchable, SoftDeletes, Trackable;

    protected $guarded = [
        'id',
    ];

    protected string $detailRouteName = 'tickets.id';

    protected array $relatedCustomEvents = [
        'ticketType',
    ];

    public static string $iconName = 'chat-bubble-left-right';

    protected function casts(): array
    {
        return [
            'state' => TicketState::class,
        ];
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
        return $this->getFirstMediaUrl('images') ?: self::icon()->getUrl();
    }
}
