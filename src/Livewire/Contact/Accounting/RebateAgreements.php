<?php

namespace FluxErp\Livewire\Contact\Accounting;

use FluxErp\Actions\RebateAgreement\CalculateRebateAgreement;
use FluxErp\Actions\RebateAgreement\DeleteRebateAgreement;
use FluxErp\Actions\RebateAgreement\SettleRebateAgreement;
use FluxErp\Actions\RebateAgreement\UpdateRebateAgreement;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\DataTables\RebateAgreementList;
use FluxErp\Livewire\Forms\RebateAgreementForm;
use FluxErp\Models\OrderType;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTable\DataTableHasFormEdit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class RebateAgreements extends RebateAgreementList
{
    use DataTableHasFormEdit {
        edit as protected dataTableHasFormEditEdit;
    }

    #[Locked]
    public ?array $calculation = null;

    #[Modelable]
    public int $contactId;

    #[Locked]
    public ?int $orderTypeId = null;

    #[DataTableForm]
    public RebateAgreementForm $rebateAgreementForm;

    protected ?string $includeBefore = 'flux::livewire.contact.accounting.rebate-agreements';

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Calculate Rebate'))
                ->icon('calculator')
                ->color('indigo')
                ->when(fn () => resolve_static(SettleRebateAgreement::class, 'canPerformAction', [false]))
                ->attributes([
                    'x-bind:disabled' => 'record.settled_at',
                    'wire:click' => 'calculate(record.id)',
                ]),
            DataTableButton::make()
                ->text(__('Edit'))
                ->icon('pencil')
                ->color('indigo')
                ->when(fn () => resolve_static(UpdateRebateAgreement::class, 'canPerformAction', [false]))
                ->attributes([
                    'x-bind:disabled' => 'record.settled_at',
                    'wire:click' => 'edit(record.id)',
                ]),
            DataTableButton::make()
                ->text(__('Delete'))
                ->icon('trash')
                ->color('red')
                ->when(fn () => resolve_static(DeleteRebateAgreement::class, 'canPerformAction', [false]))
                ->attributes([
                    'x-bind:disabled' => 'record.settled_at',
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Rebate Agreement')]),
                    'wire:click' => 'delete(record.id)',
                ]),
        ];
    }

    public function addTier(): void
    {
        $this->rebateAgreementForm->addTier();
    }

    public function calculate(string|int $id): void
    {
        try {
            $calculation = CalculateRebateAgreement::make(['id' => $id])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->calculation = $calculation->toArray();
        $this->orderTypeId = resolve_static(OrderType::class, 'query')
            ->where('order_type_enum', OrderTypeEnum::Refund)
            ->where('is_active', true)
            ->orderBy('id')
            ->value('id');

        $this->js(<<<'JS'
            $tsui.open.modal('settle-rebate-agreement-modal');
        JS);
    }

    public function edit(string|int|null $id = null): void
    {
        $this->dataTableHasFormEditEdit($id);

        $this->rebateAgreementForm->contact_id = $this->contactId;
    }

    public function removeTier(int $index): void
    {
        $this->rebateAgreementForm->removeTier($index);
    }

    #[Renderless]
    public function settle(): void
    {
        try {
            $order = SettleRebateAgreement::make([
                'id' => data_get($this->calculation, 'rebate_agreement_id'),
                'order_type_id' => $this->orderTypeId,
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->redirectRoute('orders.id', ['id' => $order->getKey()], navigate: true);
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return parent::getBuilder($builder)
            ->where('contact_id', $this->contactId);
    }
}
