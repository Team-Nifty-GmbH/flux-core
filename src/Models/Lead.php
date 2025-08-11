<?php

namespace FluxErp\Models;

use Carbon\Carbon;
use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Lead\UpdateLead;
use FluxErp\Casts\Money;
use FluxErp\Contracts\Calendarable;
use FluxErp\Contracts\Targetable;
use FluxErp\Traits\Categorizable;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\Communicatable;
use FluxErp\Traits\HasCalendarEvents;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasRecordOrigin;
use FluxErp\Traits\HasTags;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\Scout\Searchable;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\ModelStates\HasStates;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;
use TeamNiftyGmbH\DataTable\Traits\HasFrontendAttributes;

class Lead extends FluxModel implements Calendarable, HasMedia, InteractsWithDataTables, Targetable
{
    use Categorizable, Commentable, Communicatable, HasCalendarEvents, HasFrontendAttributes, HasPackageFactory,
        HasRecordOrigin, HasStates, HasTags, HasUserModification, HasUuid, InteractsWithMedia, LogsActivity, Searchable,
        SoftDeletes;

    protected $guarded = [
        'id',
    ];

    public static function aggregateColumns(string $type): array
    {
        return match ($type) {
            'count' => ['id'],
            'sum' => [
                'expected_revenue',
                'expected_gross_profit',
                'weighted_gross_profit',
                'weighted_revenue',
            ],
            'avg' => [
                'expected_revenue',
                'expected_gross_profit',
                'score',
                'weighted_gross_profit',
                'weighted_revenue',
            ],
            default => [],
        };
    }

    public static function aggregateTypes(): array
    {
        return [
            'avg',
            'count',
            'sum',
        ];
    }

    public static function fromCalendarEvent(array $event, string $action): FluxAction
    {
        return UpdateLead::make([
            'id' => data_get($event, 'id'),
            'name' => data_get($event, 'title'),
            'start' => data_get($event, 'start'),
            'end' => data_get($event, 'end'),
            'description' => data_get($event, 'description'),
        ]);
    }

    public static function ownerColumns(): array
    {
        return [
            'user_id',
            'created_by',
            'updated_by',
        ];
    }

    public static function timeframeColumns(): array
    {
        return [
            'start',
            'end',
            'created_at',
            'updated_at',
        ];
    }

    public static function toCalendar(): array
    {
        return [
            'id' => Str::of(static::class)->replace('\\', '.')->toString(),
            'modelType' => morph_alias(static::class),
            'name' => __('Leads'),
            'color' => '#a800b7',
            'resourceEditable' => false,
            'hasRepeatableEvents' => false,
            'isPublic' => false,
            'isShared' => false,
            'permission' => 'owner',
            'group' => 'other',
            'isVirtual' => true,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Lead $lead): void {
            $lead->expected_gross_profit_percentage = $lead->expected_revenue && $lead->expected_gross_profit
                ? bcround(bcdiv($lead->expected_gross_profit, $lead->expected_revenue), 4)
                : 0;

            if ($lead->isDirty('lead_state_id')
                && ! is_null(($probability = $lead->leadState()->value('probability_percentage')))
            ) {
                $lead->probability_percentage = $probability;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'start' => 'date:Y-m-d',
            'end' => 'date:Y-m-d',
            'expected_revenue' => Money::class,
            'expected_gross_profit' => Money::class,
        ];
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function addressRecommendedBy(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'recommended_by_address_id');
    }

    public function contact(): HasOneThrough
    {
        return $this->hasOneThrough(Contact::class, Address::class);
    }

    public function getAvatarUrl(): ?string
    {
        return null;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getUrl(): ?string
    {
        return route('sales.lead.id', $this->getKey());
    }

    public function leadLossReason(): BelongsTo
    {
        return $this->belongsTo(LeadLossReason::class);
    }

    public function leadState(): BelongsTo
    {
        return $this->belongsTo(LeadState::class);
    }

    public function scopeInTimeframe(
        Builder $builder,
        Carbon|string|null $start,
        Carbon|string|null $end,
        ?array $info = null
    ): void {
        $builder->where(function (Builder $query) use ($start, $end): void {
            $query->where('start', '<=', $end)
                ->where('end', '>=', $start)
                ->orWhereBetween('created_at', [$start, $end]);
        });
    }

    public function setExpectedGrossProfitAttribute(float $value): void
    {
        $this->attributes['expected_gross_profit'] = $value;
        $this->recalculateWeightedGrossProfit();
    }

    public function setExpectedRevenueAttribute(float $value): void
    {
        $this->attributes['expected_revenue'] = $value;
        $this->recalculateWeightedRevenue();
    }

    public function setProbabilityPercentageAttribute(float $value): void
    {
        $this->attributes['probability_percentage'] = $value;
        $this->recalculateWeightedRevenue();
        $this->recalculateWeightedGrossProfit();
    }

    public function toCalendarEvent(?array $info = null): array
    {
        return [
            'id' => $this->id,
            'calendar_type' => $this->getMorphClass(),
            'title' => $this->name,
            'start' => ($this->start ?? $this->created_at)->toDateTimeString(),
            'end' => $this->end?->endOfDay()->toDateTimeString(),
            'status' => $this->leadState()->value('name'),
            'invited' => [],
            'description' => $this->description,
            'allDay' => true,
            'is_editable' => true,
            'is_invited' => false,
            'is_public' => false,
            'is_repeatable' => false,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function recalculateWeightedGrossProfit(): void
    {
        $prob = data_get($this->attributes, 'probability_percentage');
        $revenue = data_get($this->attributes, 'expected_gross_profit');

        $this->attributes['weighted_gross_profit'] = ($prob !== null && $revenue !== null)
            ? bcmul($prob, $revenue)
            : null;
    }

    protected function recalculateWeightedRevenue(): void
    {
        $prob = data_get($this->attributes, 'probability_percentage');
        $revenue = data_get($this->attributes, 'expected_revenue');

        $this->attributes['weighted_revenue'] = ($prob !== null && $revenue !== null)
            ? bcmul($prob, $revenue, 2)
            : null;
    }
}
