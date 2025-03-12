<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\PaymentType\CreatePaymentType;
use FluxErp\Actions\PaymentType\DeletePaymentType;
use FluxErp\Actions\PaymentType\UpdatePaymentType;
use FluxErp\Livewire\DataTables\PaymentTypeList;
use FluxErp\Livewire\Forms\PaymentTypeForm;
use FluxErp\Models\Client;
use FluxErp\Models\PaymentType;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class PaymentTypes extends PaymentTypeList
{
    use Actions;

    public ?string $includeBefore = 'flux::livewire.settings.payment-types';

    public PaymentTypeForm $paymentType;

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('New'))
                ->icon('plus')
                ->color('indigo')
                ->when(resolve_static(CreatePaymentType::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit',
                ]),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->icon('pencil')
                ->color('indigo')
                ->when(resolve_static(UpdatePaymentType::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit(record.id)',
                ]),
            DataTableButton::make()
                ->text(__('Delete'))
                ->color('red')
                ->icon('trash')
                ->when(resolve_static(DeletePaymentType::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Currency')]),
                ]),
        ];
    }

    public function delete(PaymentType $paymentType): bool
    {
        $this->paymentType->reset();
        $this->paymentType->fill($paymentType);

        try {
            $this->paymentType->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function edit(PaymentType $paymentType): void
    {
        $this->paymentType->reset();
        $this->paymentType->fill($paymentType);

        $this->js(<<<'JS'
            $modalOpen('edit-payment-type-modal');
        JS);
    }

    public function save(): bool
    {
        try {
            $this->paymentType->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'clients' => resolve_static(Client::class, 'query')
                    ->select(['id', 'name'])
                    ->get()
                    ->toArray(),
            ]
        );
    }
}
