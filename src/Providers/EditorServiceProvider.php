<?php

namespace FluxErp\Providers;

use FluxErp\Facades\Editor;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentReminder;
use FluxErp\Models\SepaMandate;
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
                'Salutation' => '$paymentReminder->order->addressInvoice->salutation()',
                'Total Gross Price' => 'format_money($paymentReminder->order->total_gross_price, $paymentReminder->order->currency, $paymentReminder->order->addressInvoice->language)',
                'Balance' => 'format_money($paymentReminder->order->balance, $paymentReminder->order->currency, $paymentReminder->order->addressInvoice->language)',
                'Payment Reminder Dates' => '$paymentReminder->order?->paymentReminders()->pluck(\'created_at\')->map(fn ($date) => $date->isoFormat(\'L\'))->join(\', \')',
                'Last Payment Reminder Date' => '$paymentReminder->order?->paymentReminders()->latest()->whereNot(\'id\', $paymentReminder->id)->first()?->created_at?->isoFormat(\'L\')',
                'Order Number' => '$paymentReminder->order?->order_number',
                'Order Date' => '$paymentReminder->order?->order_date?->isoFormat(\'L\')',
                'Invoice Number' => '$paymentReminder->order?->invoice_number',
                'Invoice Date' => '$paymentReminder->order?->invoice_date?->isoFormat(\'L\')',
                'Client Name' => '$paymentReminder->order?->client?->name',
            ],
            PaymentReminder::class
        );

        Editor::mergeVariables(
            [
                'Salutation' => '$order->addressInvoice->salutation()',
                'Total Gross Price' => 'format_money($order->total_gross_price, $order->currency, $order->addressInvoice->language)',
                'Balance' => 'format_money($order->balance, $order->currency, $order->addressInvoice->language)',
                'Order Number' => '$order->order_number',
                'Order Date' => '$order->order_date?->isoFormat(\'L\')',
                'Invoice Number' => '$order->invoice_number',
                'Invoice Date' => '$order->invoice_date?->isoFormat(\'L\')',
                'Client Name' => '$order->client?->name',
            ],
            Order::class
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
                'Client Name' => '$sepaMandate->client->name',
                'Client Creditor Identifier' => '$sepaMandate->client->creditor_identifier',
            ],
            SepaMandate::class
        );
    }

    public function register(): void
    {
        $this->app->singleton(EditorManager::class);
    }
}
