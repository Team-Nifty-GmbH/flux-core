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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\ModelStates\HasStates;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Ticket extends Model implements HasMedia, InteractsWithDataTables
{
    use Commentable, Filterable, HasAdditionalColumns, HasCustomEvents, HasPackageFactory, HasFrontendAttributes,
        HasRelatedModel, HasSerialNumberRange, HasStates, HasUserModification, HasUuid, InteractsWithMedia, Searchable,
        SoftDeletes;

    protected $hidden = [
        'uuid',
    ];

    protected $casts = [
        'uuid' => 'string',
        'state' => TicketState::class,
    ];

    protected $guarded = [
        'id',
        'uuid',
    ];

    protected string $detailRouteName = 'tickets.id';

    protected array $relatedCustomEvents = [
        'ticketType',
    ];

    public static string $iconName = 'chat-bubble-left-right';

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
        return $this->title;
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
