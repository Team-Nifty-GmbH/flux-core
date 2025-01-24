<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Actions\Order\CreateOrder;
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

    public ?int $orderType = null;

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->color('primary')
                ->label(__('New order'))
                ->icon('plus')
                ->when(resolve_static(CreateOrder::class, 'canPerformAction', [false]))
                ->attributes([
                    'x-on:click' => "\$openModal('create-order')",
                ]),
        ];
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'priceLists' => resolve_static(PriceList::class, 'query')
                    ->get(['id', 'name'])
                    ->toArray(),
                'paymentTypes' => resolve_static(PaymentType::class, 'query')
                    ->get(['id', 'name'])
                    ->toArray(),
                'languages' => resolve_static(Language::class, 'query')
                    ->get(['id', 'name'])
                    ->toArray(),
                'clients' => resolve_static(Client::class, 'query')
                    ->where('is_active', true)
                    ->get(['id', 'name'])
                    ->toArray(),
                'orderTypes' => resolve_static(OrderType::class, 'query')
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
        $contact = resolve_static(Contact::class, 'query')
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

    #[Renderless]
    public function createDocuments(): null|MediaStream|Media
    {
        $response = $this->createDocumentFromItems($this->getSelectedModels(), true);
        $this->loadData();
        $this->reset('selected');

        return $response;
    }

    protected function getTo(OffersPrinting $item, array $documents): array
    {
        // add invoice address email if an invoice is being sent
        $address = in_array('invoice', $documents) && $item->contact->invoiceAddress
            ? $item->contact->invoiceAddress
            : $item->contact->mainAddress;

        $to = array_merge(
            [$address->email_primary],
            $address
                ->contactOptions()
                ->where('type', 'email')
                ->pluck('value')
                ->toArray()
        );

        // add primary email address if more than just the invoice is added
        if (array_diff($documents, ['invoice'])) {
            $to[] = $item->contact->mainAddress->email_primary;
        }

        return array_values(array_unique(array_filter($to)));
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
            fn () => [
                'order' => resolve_static(Order::class, 'query')
                    ->whereKey($item->getKey())
                    ->first(),
            ]
        );
    }

    protected function getPrintLayouts(): array
    {
        return resolve_static(Order::class, 'query')
            ->whereIntegerInRaw('id', $this->selected)
            ->with('orderType')
            ->get(['id', 'order_type_id'])
            ->printLayouts();
    }
}
