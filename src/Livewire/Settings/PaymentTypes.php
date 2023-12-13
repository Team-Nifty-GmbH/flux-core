<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\paymentType\CreatePaymentType;
use FluxErp\Actions\paymentType\DeletePaymentType;
use FluxErp\Actions\paymentType\UpdatePaymentType;
use FluxErp\Livewire\DataTables\PaymentTypeList;
use FluxErp\Livewire\Forms\PaymentTypeForm;
use FluxErp\Models\Client;
use FluxErp\Models\PaymentType;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class PaymentTypes extends PaymentTypeList
{
    use Actions;

    public string $view = 'flux::livewire.settings.payment-types';

    public PaymentTypeForm $paymentType;

    public function mount(): void
    {
        parent::mount();

        $this->headline = __('Payment Types');
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('New'))
                ->icon('plus')
                ->color('primary')
                ->when(CreatePaymentType::canPerformAction(false))
                ->attributes([
                    'wire:click' => 'edit',
                ]),
        ];
    }

    public function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'clients' => Client::query()
                    ->select(['id', 'name'])
                    ->get()
                    ->toArray(),
            ]
        );
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->icon('pencil')
                ->color('primary')
                ->when(UpdatePaymentType::canPerformAction(false))
                ->attributes(
                    ['wire:click' => 'edit(record.id)']
                ),
        ];
    }

    public function edit(PaymentType $paymentType): void
    {
        $this->paymentType->reset();
        $this->paymentType->fill($paymentType);

        $this->js(<<<'JS'
            $openModal('edit-payment-type');
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

    public function delete(): bool
    {
        try {
            DeletePaymentType::make($this->paymentType->toArray())
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}
