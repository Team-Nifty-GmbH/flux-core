<?php

namespace FluxErp\Providers;

use FluxErp\Facades\Editor;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentReminder;
use FluxErp\Models\PaymentType;
use FluxErp\Models\SepaMandate;
use FluxErp\Models\Ticket;
use FluxErp\Models\VatRate;
use FluxErp\Support\Editor\EditorManager;
use FluxErp\View\Components\EditorButtons\AlignCenter;
use FluxErp\View\Components\EditorButtons\AlignLeft;
use FluxErp\View\Components\EditorButtons\AlignRight;
use FluxErp\View\Components\EditorButtons\BackgroundColor;
use FluxErp\View\Components\EditorButtons\BladeVariables;
use FluxErp\View\Components\EditorButtons\Blockquote;
use FluxErp\View\Components\EditorButtons\Bold;
use FluxErp\View\Components\EditorButtons\BulletList;
use FluxErp\View\Components\EditorButtons\Code;
use FluxErp\View\Components\EditorButtons\CodeBlock;
use FluxErp\View\Components\EditorButtons\FontSize;
use FluxErp\View\Components\EditorButtons\Headings;
use FluxErp\View\Components\EditorButtons\HorizontalRule;
use FluxErp\View\Components\EditorButtons\Italic;
use FluxErp\View\Components\EditorButtons\LineHeight;
use FluxErp\View\Components\EditorButtons\Link;
use FluxErp\View\Components\EditorButtons\OrderedList;
use FluxErp\View\Components\EditorButtons\Strike;
use FluxErp\View\Components\EditorButtons\Table;
use FluxErp\View\Components\EditorButtons\TextColor;
use FluxErp\View\Components\EditorButtons\Underline;
use Illuminate\Support\ServiceProvider;

class EditorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Editor::registerButtons([
            Bold::class,
            Italic::class,
            Underline::class,
            Strike::class,
            Code::class,
            Link::class,
            Headings::class,
            FontSize::class,
            TextColor::class,
            BackgroundColor::class,
            LineHeight::class,
            HorizontalRule::class,
            BulletList::class,
            OrderedList::class,
            Blockquote::class,
            AlignLeft::class,
            AlignCenter::class,
            AlignRight::class,
            CodeBlock::class,
            Table::class,
            BladeVariables::class,
        ]);

        Editor::mergeVariables([
            'Current User Name' => 'auth()->user()?->name',
            'Current User Email' => 'auth()->user()?->email',
            'Current Date' => 'now()->isoFormat(\'L\')',
            'Current DateTime' => 'now()->isoFormat(\'L LT\')',
        ]);

        Editor::mergeVariables(
            [
                'Salutation' => '$paymentReminder->order->resolveMailableInvoiceAddress()?->salutation()',
                'Total Gross Price' => 'format_money($paymentReminder->order->total_gross_price, $paymentReminder->order->currency, $paymentReminder->order->resolveMailableInvoiceAddress()?->language)',
                'Balance' => 'format_money($paymentReminder->order->balance, $paymentReminder->order->currency, $paymentReminder->order->resolveMailableInvoiceAddress()?->language)',
                'Payment Reminder Dates' => '$paymentReminder->order?->paymentReminders()->pluck(\'created_at\')->map(fn ($date) => $date->isoFormat(\'L\'))->join(\', \')',
                'Last Payment Reminder Date' => '$paymentReminder->order?->paymentReminders()->latest()->whereNot(\'id\', $paymentReminder->id)->first()?->created_at?->isoFormat(\'L\')',
                'Order Number' => '$paymentReminder->order?->order_number',
                'Order Date' => '$paymentReminder->order?->order_date?->isoFormat(\'L\')',
                'Invoice Number' => '$paymentReminder->order?->invoice_number',
                'Invoice Date' => '$paymentReminder->order?->invoice_date?->isoFormat(\'L\')',
                'Tenant Name' => '$paymentReminder->order?->tenant?->name',
            ],
            PaymentReminder::class
        );

        Editor::mergeVariables(
            [
                'Salutation' => '$order->resolveMailableInvoiceAddress()?->salutation()',
                'Total Gross Price' => 'format_money($order->total_gross_price, $order->currency, $order->resolveMailableInvoiceAddress()?->language)',
                'Balance' => 'format_money($order->balance, $order->currency, $order->resolveMailableInvoiceAddress()?->language)',
                'Order Number' => '$order->order_number',
                'Order Date' => '$order->order_date?->isoFormat(\'L\')',
                'Invoice Number' => '$order->invoice_number',
                'Invoice Date' => '$order->invoice_date?->isoFormat(\'L\')',
                'Tenant Name' => '$order->tenant?->name',
                'Agent Name' => '$order->agent?->name',
                'Responsible User Name' => '$order->responsibleUser?->name',
                'Contact Vat Id' => '$order->contact->vat_id',
            ],
            Order::class
        );

        Editor::mergeVariables(
            [
                'Salutation' => '$order->resolveMailableInvoiceAddress()?->salutation()',
                'Total Gross Price' => 'format_money($order->total_gross_price, $order->currency, $order->resolveMailableInvoiceAddress()?->language)',
                'Balance' => 'format_money($order->balance, $order->currency, $order->resolveMailableInvoiceAddress()?->language)',
                'Order Number' => '$order->order_number',
                'Order Date' => '$order->order_date?->isoFormat(\'L\')',
                'Invoice Number' => '$order->invoice_number',
                'Invoice Date' => '$order->invoice_date?->isoFormat(\'L\')',
                'Tenant Name' => '$order->tenant?->name',
                'Agent Name' => '$order->agent?->name',
                'Responsible User Name' => '$order->responsibleUser?->name',
                'Contact Vat Id' => '$order->contact->vat_id',
            ],
            VatRate::class
        );

        Editor::mergeVariables(
            [
                'Subscription Start Date' => '$order->order_date?->isoFormat(\'L\')',
                'Subscription End Date' => '$order->calculateSubscriptionEndDate()->isoFormat(\'L\')',
                'Subscription Cycle' => '__(\Illuminate\Support\Str::headline(data_get($order->schedules()->first()?->cron, \'methods.basic\') ?? \'\'))',
            ],
            Order::class,
            'subscription'
        );

        Editor::mergeVariables(
            [
                'Salutation' => '$model->addressInvoice->salutation()',
                'Total Gross Price' => 'format_money($model->total_gross_price, $model->currency, $model->addressInvoice->language)',
                'Balance' => 'format_money($model->balance, $model->currency, $model->addressInvoice->language)',
                'Order Number' => '$model->order_number',
                'Order Date' => '$model->order_date?->isoFormat(\'L\')',
                'Invoice Number' => '$model->invoice_number',
                'Invoice Date' => '$model->invoice_date?->isoFormat(\'L\')',
                'Tenant Name' => '$model->tenant?->name',
                'Tenant Creditor Identifier' => '$model->tenant?->creditor_identifier',
                'Customer IBAN' => 'Str::iban($model->contactBankConnection?->iban)',
                'Customer Account Holder' => '$model->contactBankConnection?->account_holder',
                'Customer Bank Name' => '$model->contactBankConnection?->bank_name',
                'Customer BIC' => '$model->contactBankConnection?->bic',
                'Payment Target Days' => '$model->payment_target',
                'Payment Discount Target' => '$model->payment_discount_target',
                'Payment Discount Percent' => 'Number::percentage(bcmul($model->payment_discount_percent ?? 0, 100), precision: 2, locale: $model->addressInvoice->language)',
                'Balance Due Discount' => 'format_money($model->balance_due_discount, $model->currency, $model->addressInvoice->language)',
            ],
            PaymentType::class
        );

        Editor::mergeVariables(
            [
                'Salutation' => '$sepaMandate->contact->addressInvoice?->salutation()',
                'Customer IBAN' => '$sepaMandate->contactBankConnection?->iban',
                'Customer BIC' => '$sepaMandate->contactBankConnection?->bic',
                'Customer Bank Name' => '$sepaMandate->contactBankConnection?->bank_name',
                'Customer Account Holder' => '$sepaMandate->contactBankConnection?->account_holder',
                'Mandate Reference Number' => '$sepaMandate->mandate_reference_number',
                'Sepa Mandate Type Enum' => '__($sepaMandate->sepa_mandate_type_enum->value)',
                'Tenant Name' => '$sepaMandate->tenant->name',
                'Tenant Creditor Identifier' => '$sepaMandate->tenant->creditor_identifier',
            ],
            SepaMandate::class
        );

        Editor::mergeVariables(
            [
                'Ticket Number' => '$ticket->ticket_number',
                'Ticket Title' => '$ticket->title',
                'Ticket Description' => '$ticket->description',
                'Ticket Type' => '$ticket->ticketType?->name',
                'Customer Name' => '$ticket->authenticatable?->getLabel()',
                'Customer Email' => '$ticket->authenticatable?->email ?? $ticket->authenticatable?->email_primary',
                'Created At' => '$ticket->created_at?->isoFormat(\'L LT\')',
            ],
            Ticket::class
        );
    }

    public function register(): void
    {
        $this->app->singleton(EditorManager::class);
    }
}
