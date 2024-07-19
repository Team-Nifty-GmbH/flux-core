<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Actions\Order\DeleteOrder;
use FluxErp\Contracts\OffersPrinting;
use FluxErp\Livewire\Forms\OrderForm;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Language;
use FluxErp\Models\Media;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Traits\Livewire\CreatesDocuments;
use Illuminate\Validation\ValidationException;
use Laravel\SerializableClosure\SerializableClosure;
use Livewire\Attributes\Renderless;
use Spatie\MediaLibrary\Support\MediaStream;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class OrderList extends \FluxErp\Livewire\DataTables\OrderList
{
    use CreatesDocuments;

    protected string $view = 'flux::livewire.order.order-list';

    public ?string $cacheKey = 'order.order-list';

    public OrderForm $order;

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->color('primary')
                ->label(__('New order'))
                ->icon('plus')
                ->attributes([
                    'x-on:click' => "\$openModal('create-order')",
                ]),
        ];
    }

    public function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'priceLists' => app(PriceList::class)->query()
                    ->get(['id', 'name'])
                    ->toArray(),
                'paymentTypes' => app(PaymentType::class)->query()
                    ->get(['id', 'name'])
                    ->toArray(),
                'languages' => app(Language::class)->query()
                    ->get(['id', 'name'])
                    ->toArray(),
                'clients' => app(Client::class)->query()
                    ->where('is_active', true)
                    ->get(['id', 'name'])
                    ->toArray(),
                'orderTypes' => app(OrderType::class)->query()
                    ->where('is_hidden', false)
                    ->where('is_active', true)
                    ->get(['id', 'name'])
                    ->toArray(),
            ]
        );
    }

    protected function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->icon('document-text')
                ->label(__('Create Documents'))
                ->color('primary')
                ->wireClick('openCreateDocumentsModal'),
            DataTableButton::make()
                ->icon('trash')
                ->label(__('Delete'))
                ->color('negative')
                ->when(fn () => resolve_static(DeleteOrder::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete',
                    'wire:flux-confirm.icon.error' => __('wire:confirm.delete', ['model' => __('Orders')]),
                ]),
        ];
    }

    #[Renderless]
    public function fetchContactData(): void
    {
        $contact = app(Contact::class)->query()
            ->whereKey($this->order->contact_id)
            ->first();

        $this->order->client_id = $contact->client_id ?: $this->order->client_id;
        $this->order->agent_id = $contact->agent_id ?: $this->order->agent_id;
        $this->order->address_invoice_id = $contact->address_invoice_id;
        $this->order->address_delivery_id = $contact->address_delivery_id;
        $this->order->price_list_id = $contact->price_list_id ?: $this->order->price_list_id;
        $this->order->payment_type_id = $contact->payment_type_id ?: $this->order->payment_type_id;
        $this->order->address_invoice_id = $contact->invoice_address_id ?: $this->order->address_invoice_id;
        $this->order->address_delivery_id = $contact->delivery_address_id ?: $this->order->address_delivery_id;
    }

    public function save(): ?false
    {
        try {
            $this->order->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->redirect(route('orders.id', $this->order->id), true);

        return null;
    }

    protected function getTo(OffersPrinting $item, array $documents): array
    {
        $to = [];

        $to[] = in_array('invoice', $documents) && $item->contact->invoiceAddress
            ? $item->contact->invoiceAddress->email_primary
            : $item->contact->mainAddress->email_primary;

        if (array_keys($this->selectedPrintLayouts['email']) !== ['invoice']
            && $item->contact->mainAddress->email_primary
        ) {
            $to[] = $item->contact->mainAddress->email_primary;
        }

        return $to;
    }

    protected function getSubject(OffersPrinting $item): string
    {
        return html_entity_decode(
            $item->orderType->mail_subject ?? '{{ $order->orderType->name }} {{ $order->order_number }}'
        );
    }

    protected function getHtmlBody(OffersPrinting $item): string
    {
        return html_entity_decode($item->orderType->mail_body);
    }

    protected function getBladeParameters(OffersPrinting $item): array|SerializableClosure|null
    {
        return new SerializableClosure(
            fn () => ['order' => app(Order::class)->whereKey($item->getKey())->first()]
        );
    }

    protected function getPrintLayouts(): array
    {
        return app(Order::class)->query()
            ->whereIntegerInRaw('id', $this->selected)
            ->with('orderType')
            ->get(['id', 'order_type_id'])
            ->printLayouts();
    }

    public function createDocuments(): null|MediaStream|Media
    {
        $response = $this->createDocumentFromItems($this->getSelectedModels());
        $this->loadData();
        $this->selected = [];

        return $response;
    }
}
