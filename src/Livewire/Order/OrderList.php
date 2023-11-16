<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\Forms\OrderForm;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Language;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportRedirects\Redirector;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class OrderList extends \FluxErp\Livewire\DataTables\OrderList
{
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
                'priceLists' => PriceList::query()
                    ->get(['id', 'name'])
                    ->toArray(),
                'paymentTypes' => PaymentType::query()
                    ->get(['id', 'name'])
                    ->toArray(),
                'languages' => Language::query()
                    ->get(['id', 'name'])
                    ->toArray(),
                'clients' => Client::query()
                    ->get(['id', 'name'])
                    ->toArray(),
                'orderTypes' => OrderType::query()
                    ->get(['id', 'name'])
                    ->toArray(),
            ]
        );
    }

    public function updatedOrderContactId(): void
    {
        $contact = Contact::query()->whereKey($this->order->contact_id)->first();

        $this->order->client_id = $contact->client_id ?: $this->order->client_id;
        $this->order->agent_id = $contact->agent_id ?: $this->order->agent_id;
        $this->order->address_invoice_id = $contact->address_invoice_id;
        $this->order->address_delivery_id = $contact->address_delivery_id;
        $this->order->language_id = $contact->language_id ?: $this->order->language_id;
        $this->order->price_list_id = $contact->price_list_id ?: $this->order->price_list_id;
        $this->order->payment_type_id = $contact->payment_type_id ?: $this->order->payment_type_id;

        $paymentType = PaymentType::query()->whereKey($this->order->payment_type_id)->first();

        $this->order->payment_reminder_days_1 = $contact->payment_reminder_days_1
            ?: $paymentType->payment_reminder_days_1
            ?: 1;
        $this->order->payment_reminder_days_2 = $contact->payment_reminder_days_2
            ?: $paymentType->payment_reminder_days_2
            ?: 1;
        $this->order->payment_reminder_days_3 = $contact->payment_reminder_days_3
            ?: $paymentType->payment_reminder_days_3
            ?: 1;
        $this->order->payment_target = $contact->payment_target_days + 0
            ?: $paymentType->payment_target + 0;
        $this->order->payment_discount_target = $contact->discount_days
            ?: $paymentType->payment_discount_target;
        $this->order->payment_discount_percent = $contact->discount_percent
            ?: $paymentType->payment_discount_percentage;

        $this->render();
    }

    public function save(): false|Redirector
    {
        try {
            $this->order->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        return redirect()->to(route('orders.id', $this->order->id));
    }
}
