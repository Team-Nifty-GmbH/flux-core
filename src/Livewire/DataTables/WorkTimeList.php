<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Actions\Order\UpdateOrder;
use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Actions\WorkTime\DeleteWorkTime;
use FluxErp\Actions\WorkTime\UpdateLockedWorkTime;
use FluxErp\Livewire\Forms\CreateOrdersFromWorkTimesForm;
use FluxErp\Livewire\Forms\LockedWorkTimeForm;
use FluxErp\Models\Contact;
use FluxErp\Models\OrderType;
use FluxErp\Models\Product;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WorkTime;
use FluxErp\Models\WorkTimeType;
use FluxErp\Traits\Trackable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\ModelInfo\ModelInfo;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use TeamNiftyGmbH\DataTable\Traits\HasEloquentListeners;

class WorkTimeList extends DataTable
{
    use HasEloquentListeners;

    protected string $model = WorkTime::class;

    protected ?string $includeBefore = 'flux::livewire.datatables.work-time-list.include-before';

    protected string $view = 'flux::livewire.work-time.work-time-list';

    public LockedWorkTimeForm $workTime;

    public CreateOrdersFromWorkTimesForm $createOrdersFromWorkTimes;

    public array $enabledCols = [
        'user.name',
        'name',
        'total_time_ms',
        'paused_time_ms',
        'started_at',
        'ended_at',
        'is_locked',
        'is_daily_work_time',
    ];

    public array $formatters = [
        'total_time_ms' => 'time',
        'paused_time_ms' => 'time',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public bool $isSelectable = true;

    public function itemToArray($item): array
    {
        $item = parent::itemToArray($item);
        $item['name'] = __($item['name']);

        return $item;
    }

    public function getReturnKeys(): array
    {
        return array_merge(
            parent::getReturnKeys(),
            ['name']
        );
    }

    public function getAggregatable(): array
    {
        return array_merge(parent::getAggregatable(), ['paused_time_ms', 'total_time_ms']);
    }

    public function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create Orders'))
                ->color('primary')
                ->xOnClick(<<<'JS'
                    $openModal('create-orders');
                JS)
                ->when(fn () => CreateOrder::canPerformAction(false)),
        ];
    }

    public function createOrders(): void
    {
        try {
            $this->createOrdersFromWorkTimes->validate();
            $product = Product::query()
                ->whereKey($this->createOrdersFromWorkTimes->product_id)
                ->firstOrFail();
        } catch (ValidationException|ModelNotFoundException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $roundMs = bcmul($this->createOrdersFromWorkTimes->round_to_minute, 60 * 1000);

        $selectedIds = $this->getSelectedValues();

        // get all contact_ids from selected work times
        $contactIds = WorkTime::query()
            ->whereIntegerInRaw('id', $selectedIds)
            ->whereNotNull('contact_id')
            ->distinct('contact_id')
            ->pluck('contact_id');
        $contacts = Contact::query()
            ->whereIntegerInRaw('id', $contactIds)
            ->with('client')
            ->get();

        $orderIds = [];
        $billedWorkTimes = 0;
        foreach ($contacts as $contact) {
            $workTimes = WorkTime::query()
                ->whereIntegerInRaw('id', $selectedIds)
                ->where('contact_id', $contact->id)
                ->where('is_locked', true)
                ->where('is_daily_work_time', false)
                ->where('total_time_ms', '>', 0)
                ->whereNull('order_position_id')
                ->orderBy('is_billable', 'desc')
                ->orderBy('started_at', 'desc')
                ->when(
                    ! $this->createOrdersFromWorkTimes->add_non_billable_work_times,
                    fn ($query) => $query->where('is_billable', true)
                )
                ->get();

            if ($workTimes->isEmpty()) {
                continue;
            }

            $billedWorkTimes += $workTimes->count();

            try {
                $order = CreateOrder::make([
                    'client_id' => $contact->client_id,
                    'contact_id' => $contact->id,
                    'order_type_id' => $this->createOrdersFromWorkTimes->order_type_id,
                ])
                    ->validate()
                    ->execute();
            } catch (ValidationException $e) {
                exception_to_notifications($e, $this);

                continue;
            }

            $orderIds[] = $order->id;
            $smallestStartedAt = null;
            $greatestEndedAt = null;
            foreach ($workTimes as $workTime) {
                if ($this->createOrdersFromWorkTimes->round == 'ceil') {
                    $time = bcmul(bcceil(bcdiv($workTime->total_time_ms, $roundMs)), $roundMs);
                } elseif ($this->createOrdersFromWorkTimes->round == 'floor') {
                    $time = bcmul(bcfloor(bcdiv($workTime->total_time_ms, $roundMs)), $roundMs);
                } else {
                    $time = bcmul(bcround(bcdiv($workTime->total_time_ms, 60000)), 60000);
                }

                $billingAmount = bcround($product->time_unit_enum->convertFromMilliseconds($time), 2);

                try {
                    $prefix = ($workTime->workTimeType?->name
                        ? __('Type') . ': ' . $workTime->workTimeType->name . '<br/>'
                        : ''
                    );
                    $description = $prefix
                        . __('Date') . ': '
                        . $workTime->started_at
                            ->locale($contact->invoiceAddress->language->language_code)
                            ->isoFormat('L')
                        . '<br/>'
                        . __('User') . ': ' . $workTime->user->name
                        . '<br/><br/>'
                        . $workTime->description;

                    $orderPosition = CreateOrderPosition::make([
                        'name' => $workTime->name,
                        'description' => $description,
                        'warehouse_id' => Warehouse::default()?->id,
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'amount' => $billingAmount,
                        'discount_percentage' => ! $workTime->is_billable ? 1 : null,
                    ])->validate()->execute();
                } catch (ValidationException $e) {
                    exception_to_notifications($e, $this);

                    continue;
                }

                try {
                    UpdateLockedWorkTime::make([
                        'id' => $workTime->id,
                        'order_position_id' => $orderPosition->id,
                    ])->validate()->execute();
                } catch (ValidationException $e) {
                    exception_to_notifications($e, $this);

                    continue;
                }

                // Check and update the smallest started_at
                if (is_null($smallestStartedAt) || $workTime->started_at->lt($smallestStartedAt)) {
                    $smallestStartedAt = $workTime->started_at->startOfDay();
                }

                // Check and update the greatest ended_at
                if (is_null($greatestEndedAt) || $workTime->ended_at->gt($greatestEndedAt)) {
                    $greatestEndedAt = $workTime->ended_at->startOfDay();
                }
            }

            if ($smallestStartedAt->lt($greatestEndedAt)) {
                try {
                    UpdateOrder::make([
                        'id' => $order->id,
                        'system_delivery_date' => $smallestStartedAt,
                        'system_delivery_date_end' => ($greatestEndedAt ?? now())->format('Y-m-d'),
                    ])->validate()->execute();
                } catch (ValidationException $e) {
                    exception_to_notifications($e, $this);
                }
            }

            $order->calculatePrices()->save();
        }

        $this->selected = [];
        $this->notification()->success(
            count($orderIds) . ' ' . __('Orders created'),
            __('Resulting from :count billable work times', ['count' => $billedWorkTimes])
        );

        $this->loadData();
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->icon('pencil')
                ->color('primary')
                ->wireClick('edit(record.id)')
                ->when(UpdateLockedWorkTime::canPerformAction(false)),
            DataTableButton::make()
                ->label(__('Delete'))
                ->icon('trash')
                ->color('negative')
                ->when(DeleteWorkTime::canPerformAction(false))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:confirm.icon.error' => __('wire:confirm.delete', ['model' => __('Work Time')]),
                ]),
        ];
    }

    public function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'workTimeTypes' => WorkTimeType::query()
                    ->get(['id', 'name', 'is_billable'])
                    ->toArray(),
                'trackableTypes' => model_info_all()
                    ->filter(
                        fn (ModelInfo $modelInfo) => in_array(Trackable::class, $modelInfo->traits->toArray())
                    )
                    ->map(fn (ModelInfo $modelInfo) => $modelInfo->class)
                    ->toArray(),
                'orderTypes' => OrderType::query()
                    ->where('is_hidden', false)
                    ->where('is_active', true)
                    ->get(['id', 'name', 'order_type_enum'])
                    ->filter(fn (OrderType $orderType) => ! $orderType->order_type_enum->isPurchase())
                    ->pluck('name', 'id')
                    ->toArray(),
            ]
        );
    }

    #[Renderless]
    public function edit(WorkTime $workTime): void
    {
        $this->workTime->reset();
        $this->workTime->fill($workTime);

        $this->js(<<<'JS'
            $openModal('edit-work-time');
        JS);
    }

    #[Renderless]
    public function delete(WorkTime $workTime): void
    {
        $this->workTime->reset();
        $this->workTime->fill($workTime);

        try {
            $this->workTime->delete();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->loadData();
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->workTime->save();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}
