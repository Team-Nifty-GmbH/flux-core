<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\PaymentReminderText\CreatePaymentReminderText;
use FluxErp\Actions\PaymentReminderText\DeletePaymentReminderText;
use FluxErp\Actions\PaymentReminderText\UpdatePaymentReminderText;
use FluxErp\Livewire\DataTables\PaymentReminderTextList;
use FluxErp\Livewire\Forms\PaymentReminderTextForm;
use FluxErp\Models\PaymentReminderText;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class PaymentReminderTexts extends PaymentReminderTextList
{
    public PaymentReminderTextForm $paymentReminderTextForm;

    protected ?string $includeBefore = 'flux::livewire.settings.payment-reminder-texts';

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('New'))
                ->icon('plus')
                ->color('indigo')
                ->when(resolve_static(CreatePaymentReminderText::class, 'canPerformAction', [false]))
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
                ->when(resolve_static(UpdatePaymentReminderText::class, 'canPerformAction', [false]))
                ->wireClick('edit(record.id)'),
            DataTableButton::make()
                ->text(__('Delete'))
                ->icon('trash')
                ->color('red')
                ->when(resolve_static(DeletePaymentReminderText::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Payment Reminder Text')]),
                    'wire:click' => 'delete(record.id)',
                ]),
        ];
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

    public function edit(PaymentReminderText $paymentReminderText): void
    {
        $this->paymentReminderTextForm->reset();
        $this->paymentReminderTextForm->fill($paymentReminderText);

        $this->js(<<<'JS'
            $modalOpen('edit-payment-reminder-text-modal');
        JS);
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
}
