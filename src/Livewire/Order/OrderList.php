<?php

namespace FluxErp\Livewire\Order;

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
use Livewire\Features\SupportRedirects\Redirector;

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
        'payment_reminder_days_1' => 0,
        'payment_reminder_days_2' => 0,
        'payment_reminder_days_3' => 0,
    ];

    public array $priceLists = [];

    public array $paymentTypes = [];

    public array $languages = [];

    public array $clients = [];

    public array $orderTypes = [];

    public array $filters = [];

    public function getRules(): array
    {
        return Arr::prependKeysWith((new CreateOrderRequest())->rules(), 'order.');
    }

    public function updatedOrderContactId(): void
    {
        $contact = Contact::query()->whereKey($this->order['contact_id'])->first();

        $this->order['client_id'] = $contact->client_id ?: $this->order['client_id'];
        $this->order['address_invoice_id'] = $contact->address_invoice_id;
        $this->order['address_delivery_id'] = $contact->address_delivery_id;
        $this->order['language_id'] = $contact->language_id ?: $this->order['language_id'];
        $this->order['price_list_id'] = $contact->price_list_id ?: $this->order['price_list_id'];
        $this->order['payment_type_id'] = $contact->payment_type_id ?: $this->order['payment_type_id'];

        $paymentType = PaymentType::query()->whereKey($this->order['payment_type_id'])->first();

        $this->order['payment_reminder_days_1'] = $contact->payment_reminder_days_1
            ?: $paymentType->payment_reminder_days_1
            ?: 1;
        $this->order['payment_reminder_days_2'] = $contact->payment_reminder_days_2
            ?: $paymentType->payment_reminder_days_2
            ?: 1;
        $this->order['payment_reminder_days_3'] = $contact->payment_reminder_days_3
            ?: $paymentType->payment_reminder_days_3
            ?: 1;
        $this->order['payment_target'] = $contact->payment_target_days + 0
            ?: $paymentType->payment_target + 0;
        $this->order['payment_discount_target'] = $contact->discount_days
            ?: $paymentType->payment_discount_target;
        $this->order['payment_discount_percent'] = $contact->discount_percent
            ?: $paymentType->payment_discount_percentage;
    }

    public function mount(): void
    {
        $this->priceLists = PriceList::query()
            ->get(['id', 'name'])
            ->toArray();
        if (count($this->priceLists) === 1) {
            $this->order['price_list_id'] = $this->priceLists[0]['id'];
        }

        $this->paymentTypes = PaymentType::query()
            ->get(['id', 'name'])
            ->toArray();
        if (count($this->paymentTypes) === 1) {
            $this->order['payment_type_id'] = $this->paymentTypes[0]['id'];
        }

        $this->languages = Language::query()
            ->get(['id', 'name'])
            ->toArray();
        if (count($this->languages) === 1) {
            $this->order['language_id'] = $this->languages[0]['id'];
        }

        $this->clients = Client::query()
            ->get(['id', 'name'])
            ->toArray();
        if (count($this->clients) === 1) {
            $this->order['client_id'] = $this->clients[0]['id'];
        }

        $this->orderTypes = OrderType::query()
            ->get(['id', 'name'])
            ->toArray();
        if (count($this->orderTypes) === 1) {
            $this->order['order_type_id'] = $this->orderTypes[0]['id'];
        }
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
