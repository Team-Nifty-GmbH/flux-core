<?php

namespace FluxErp\Http\Livewire\Order;

use Carbon\Carbon;
use FluxErp\Http\Requests\CreateOrderRequest;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Language;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Services\OrderService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Livewire\Component;
use Livewire\Redirector;

class OrderList extends Component
{
    public array $order = [
        'contact_id' => null,
        'address_invoice_id' => null,
        'address_delivery_id' => null,
        'client_id' => null,
        'price_list_id' => null,
        'payment_type_id' => null,
        'language_id' => null,
        'order_type_id' => null,
        'payment_texts' => [],
    ];

    public array $priceLists = [];

    public array $paymentTypes = [];

    public array $languages = [];

    public array $clients = [];

    public array $orderTypes = [];

    public function getRules(): array
    {
        return Arr::prependKeysWith((new CreateOrderRequest())->rules(), 'order.');
    }

    public function updatedOrderContactId(): void
    {
        $contact = Contact::query()->whereKey($this->order['contact_id'])->first();

        $this->order['client_id'] = $contact->client_id;
        $this->order['address_invoice_id'] = $contact->address_invoice_id;
        $this->order['address_delivery_id'] = $contact->address_delivery_id;
        $this->order['language_id'] = $contact->language_id;
        $this->order['price_list_id'] = $contact->price_list_id;
        $this->order['payment_type_id'] = $contact->payment_type_id;
        $this->order['payment_reminder_days_1'] = $contact->payment_reminder_days_1;
        $this->order['payment_reminder_days_2'] = $contact->payment_reminder_days_2;
        $this->order['payment_reminder_days_3'] = $contact->payment_reminder_days_3;
        $this->order['payment_target'] = $contact->payment_target + 0;
    }

    public function mount(): void
    {
        $this->priceLists = PriceList::query()
            ->get(['id', 'name'])
            ->toArray();

        $this->paymentTypes = PaymentType::query()
            ->get(['id', 'name'])
            ->toArray();

        $this->languages = Language::query()
            ->get(['id', 'name'])
            ->toArray();

        $this->clients = Client::query()
            ->get(['id', 'name'])
            ->toArray();

        $this->orderTypes = OrderType::query()
            ->get(['id', 'name'])
            ->toArray();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.order.order-list');
    }

    public function save(): Redirector
    {
        $validated = $this->validate()['order'];

        $validated['order_date'] = Carbon::now()->format('Y-m-d');
        $order = (new OrderService())->create($validated);

        return redirect()->to($order->detailRoute());
    }
}
