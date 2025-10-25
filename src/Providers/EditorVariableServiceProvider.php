<?php

namespace FluxErp\Providers;

use FluxErp\Facades\EditorVariable;
use FluxErp\Models;
use FluxErp\Support\EditorVariableManager;
use Illuminate\Support\ServiceProvider;

class EditorVariableServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        EditorVariable::merge([
            'Current User Name' => 'auth()->user()?->name',
            'Current User Email' => 'auth()->user()?->email',
            'Current Date' => 'now()->isoFormat(\'L\')',
            'Current DateTime' => 'now()->isoFormat(\'L LT\')',
        ]);

        EditorVariable::merge(
            [
                'Salutation' => '$paymentReminder->order->addressInvoice->salutation()',
                'Total Gross Price' => 'format_money($paymentReminder->order->total_gross_price, $paymentReminder->order->currency, $paymentReminder->order->addressInvoice->language)',
                'Balance' => 'format_money($paymentReminder->order->balance, $paymentReminder->order->currency, $paymentReminder->order->addressInvoice->language)',
                'Last Payment Reminder Date' => '$paymentReminder->order->paymentReminders()->latest()->whereNot(\'id\', $paymentReminder->id)->first()?->created_at?->isoFormat(\'L\')',
                'Invoice Number' => '$paymentReminder->order->invoice_number',
                'Invoice Date' => '$paymentReminder->order->invoice_date->isoFormat(\'L\')',
            ],
            Models\PaymentReminder::class
        );

        EditorVariable::merge(
            [
                'Salutation' => '$order->addressInvoice->salutation()',
                'Total Gross Price' => 'format_money($order->total_gross_price, $order->currency, $order->addressInvoice->language)',
                'Balance' => 'format_money($order->balance, $order->currency, $order->addressInvoice->language)',
                'Invoice Number' => '$order->invoice_number',
                'Invoice Date' => '$order->invoice_date->isoFormat(\'L\')',
            ],
            Models\Order::class
        );

        EditorVariable::merge(
            [
                'Salutation' => '$sepaMandate->contact->addressInvoice?->salutation()',
                'Customer IBAN' => '$sepaMandate->contactBankConnection?->iban',
                'Customer BIC' => '$sepaMandate->contactBankConnection?->bic',
                'Customer Bank Name' => '$sepaMandate->contactBankConnection?->bank_name',
                'Customer Account Holder' => '$sepaMandate->contactBankConnection?->account_holder',
                'Mandate Reference Number' => '$sepaMandate->mandate_reference_number',
                'Sepa Mandate Type Enum' => '__($sepaMandate->sepa_mandate_type_enum->value)',
            ],
            Models\SepaMandate::class
        );
    }

    public function register(): void
    {
        $this->app->singleton(EditorVariableManager::class, function (): EditorVariableManager {
            return new EditorVariableManager();
        });
    }
}
