<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Actions\FixedAsset\CreateFixedAsset;
use FluxErp\Actions\FixedAsset\DeleteFixedAsset;
use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Livewire\DataTables\FixedAssetList;
use FluxErp\Livewire\Forms\FixedAssetForm;
use FluxErp\Models\FixedAsset;
use FluxErp\Models\OrderPosition;
use FluxErp\Rulesets\FixedAsset\CreateFixedAssetRuleset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class FixedAssets extends FixedAssetList
{
    public ?string $includeBefore = 'flux::livewire.accounting.fixed-assets';

    public FixedAssetForm $fixedAsset;

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('New'))
                ->icon('plus')
                ->color('indigo')
                ->when(resolve_static(CreateFixedAsset::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit()',
                ]),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Delete'))
                ->icon('trash')
                ->color('red')
                ->when(resolve_static(DeleteFixedAsset::class, 'canPerformAction', [false]))
                ->attributes([
                    'x-cloak',
                    'x-show' => '! record.has_booked_depreciations && ! record.disposed_at',
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Fixed Asset')]),
                    'wire:click' => 'delete(record.id)',
                ]),
        ];
    }

    #[Renderless]
    public function changedOrderPosition(int $orderPositionId): void
    {
        $orderPosition = resolve_static(OrderPosition::class, 'query')
            ->with([
                'order:id,contact_id,invoice_date,system_delivery_date',
                'ledgerAccount:id,ledger_account_type_enum',
            ])
            ->whereKey($orderPositionId)
            ->first(['id', 'order_id', 'tenant_id', 'ledger_account_id', 'name', 'total_net_price']);

        if (Validator::make(
            ['order_id' => $orderPosition?->order_id],
            ['order_id' => data_get(resolve_static(CreateFixedAssetRuleset::class, 'getRules'), 'order_id')]
        )->fails()) {
            $this->fixedAsset->order_position_id = null;

            $this->notification()
                ->error(__('Only positions of purchase orders can become fixed assets.'))
                ->send();

            return;
        }

        $this->fixedAsset->fill([
            'tenant_id' => $orderPosition->tenant_id,
            'order_id' => $orderPosition->order_id,
            'contact_id' => $orderPosition->order->contact_id,
            'ledger_account_id' => $orderPosition->ledgerAccount?->ledger_account_type_enum === LedgerAccountTypeEnum::Asset
                ? $orderPosition->ledger_account_id
                : $this->fixedAsset->ledger_account_id,
            'name' => $orderPosition->name,
            'acquisition_cost' => (float) bcround(
                bcabs((string) ($orderPosition->getRawOriginal('total_net_price') ?? 0)),
                2
            ),
            'acquired_at' => ($orderPosition->order->system_delivery_date ?? $orderPosition->order->invoice_date)
                ?->toDateString(),
        ]);
    }

    public function delete(FixedAsset $fixedAsset): bool
    {
        $this->fixedAsset->reset();
        $this->fixedAsset->fill($fixedAsset);

        try {
            $this->fixedAsset->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this, form: $this->fixedAsset);

            return false;
        }

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function edit(): void
    {
        $this->fixedAsset->reset();

        $this->modalOpen('edit-fixed-asset-modal');
    }

    public function save(): bool
    {
        try {
            $this->fixedAsset->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this, form: $this->fixedAsset);

            return false;
        }

        $this->redirect(route('accounting.fixed-assets.id', ['id' => $this->fixedAsset->id]), navigate: true);

        return true;
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->withExists(['depreciations as has_booked_depreciations' => fn (Builder $query): Builder => $query->booked()]);
    }

    protected function getReturnKeys(): array
    {
        return array_merge(parent::getReturnKeys(), ['disposed_at', 'has_booked_depreciations']);
    }
}
