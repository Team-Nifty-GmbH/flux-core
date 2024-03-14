<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\PaymentReminderText\CreatePaymentReminderText;
use FluxErp\Actions\PaymentReminderText\DeletePaymentReminderText;
use FluxErp\Livewire\DataTables\PaymentReminderTextList;
use FluxErp\Livewire\Forms\PaymentReminderTextForm;
use FluxErp\Models\PaymentReminderText;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class PaymentReminderTexts extends PaymentReminderTextList
{
    protected ?string $includeBefore = 'flux::livewire.settings.payment-reminder-texts';

    public PaymentReminderTextForm $paymentReminderTextForm;

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('New'))
                ->icon('plus')
                ->color('primary')
                ->when(resolve_static(CreatePaymentReminderText::class, 'canPerformAction', [false]))
                ->attributes(
                    ['wire:click' => 'edit']
                ),
        ];
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->icon('pencil')
                ->color('primary')
                ->when(resolve_static(CreatePaymentReminderText::class, 'canPerformAction', [false]))
                ->wireClick('edit(record.id)'),
            DataTableButton::make()
                ->label(__('Delete'))
                ->icon('trash')
                ->color('negative')
                ->when(resolve_static(DeletePaymentReminderText::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:confirm.icon.error' => __('wire:confirm.delete', ['model' => __('Payment Reminder Text')]),
                    'wire:click' => 'delete(record.id)',
                ]),
        ];
    }

    public function save(): bool
    {
        try {
            $this->paymentReminderTextForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function edit(PaymentReminderText $paymentReminderText): void
    {
        $this->paymentReminderTextForm->reset();
        $this->paymentReminderTextForm->fill($paymentReminderText);

        $this->js(<<<'JS'
            $openModal('edit-payment-reminder-text');
        JS);
    }

    public function delete(PaymentReminderText $paymentReminderText): bool
    {
        $this->paymentReminderTextForm->reset();
        $this->paymentReminderTextForm->fill($paymentReminderText);

        try {
            DeletePaymentReminderText::make($this->paymentReminderTextForm)
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
