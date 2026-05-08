<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Actions\PurchaseInvoice\CreatePurchaseInvoice;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use FluxErp\Traits\Livewire\WithDocumentScanning;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class UnassignedPurchaseInvoices extends Component
{
    use Widgetable, WithDocumentScanning;

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    public static function getCategory(): ?string
    {
        return 'Finance';
    }

    public static function getDefaultHeight(): int
    {
        return 3;
    }

    public static function getDefaultWidth(): int
    {
        return 2;
    }

    public function render(): View|Factory
    {
        return view('flux::livewire.widgets.unassigned-purchase-invoices');
    }

    #[Computed]
    public function purchaseInvoices(): array
    {
        return resolve_static(PurchaseInvoice::class, 'query')
            ->whereNull('order_id')
            ->with([
                'contact.invoiceAddress',
                'currency:id,iso',
                'invoice:id,file_name',
            ])
            ->orderByDesc('invoice_date')
            ->get([
                'id',
                'contact_id',
                'currency_id',
                'media_id',
                'invoice_date',
                'invoice_number',
                'total_gross_price',
            ])
            ->map(fn (PurchaseInvoice $invoice): array => $this->toListItem($invoice))
            ->all();
    }

    #[Renderless]
    public function goToPurchaseInvoice(int $id): void
    {
        session()->put('open_purchase_invoice_id', $id);

        $this->redirect(
            route('accounting.purchase-invoices'),
            navigate: true,
        );
    }

    public function placeholder(): View|Factory
    {
        return view('flux::livewire.placeholders.horizontal-bar');
    }

    protected function getScannedDocumentAction(): string
    {
        return CreatePurchaseInvoice::class;
    }

    protected function toListItem(PurchaseInvoice $invoice): array
    {
        $supplier = data_get($invoice, 'contact.invoiceAddress')?->getLabel();
        $invoiceNumber = $invoice->invoice_number;
        $filename = data_get($invoice, 'invoice.file_name');

        $name = $supplier ?: $invoiceNumber ?: $filename ?: '#' . $invoice->getKey();

        $currencyIso = data_get($invoice, 'currency.iso');
        $captionParts = array_filter([
            $invoice->invoice_date?->format('d.m.Y'),
            ! is_null($invoice->total_gross_price) && ! blank($currencyIso)
                ? Number::currency(
                    (float) $invoice->total_gross_price,
                    $currencyIso,
                    app()->getLocale(),
                )
                : null,
            $supplier && $invoiceNumber ? $invoiceNumber : null,
        ]);

        return [
            'id' => $invoice->getKey(),
            'name' => $name,
            'caption' => implode(' · ', $captionParts),
        ];
    }
}
