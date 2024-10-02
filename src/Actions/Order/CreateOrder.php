<?php

namespace FluxErp\Actions\Order;

use FluxErp\Actions\FluxAction;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\Order\CreateOrderRuleset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateOrder extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateOrderRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Order::class];
    }

    public function performAction(): Order
    {
        $users = Arr::pull($this->data, 'users');
        $addresses = Arr::pull($this->data, 'addresses', []);

        $order = app(Order::class, ['attributes' => $this->data]);
        if ($order->shipping_costs_net_price) {
            $order->shipping_costs_vat_rate_percentage = 0.190000000;   // TODO: Make this percentage NOT hardcoded!
            $order->shipping_costs_gross_price = net_to_gross(
                $order->shipping_costs_net_price,
                $order->shipping_costs_vat_rate_percentage
            );
            $order->shipping_costs_vat_price = bcsub(
                $order->shipping_costs_gross_price,
                $order->shipping_costs_net_price
            );
        }

        $order->save();

        if ($addresses) {
            $addresses = collect($addresses)
                ->unique(fn ($address) => $address['address_id'] . '_' . $address['address_type_id'])
                ->toArray();

            $order->addresses()->attach($addresses);
        }

        if ($users) {
            $order->users()->sync($users);
        }

        return $order->refresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['currency_id'] ??= Currency::default()?->id;

        if (! data_get($this->data, 'address_invoice_id', false)
            && $contactId = data_get($this->data, 'contact_id', false)
        ) {
            $contact = resolve_static(Contact::class, 'query')
                ->whereKey($contactId)
                ->with(['invoiceAddress', 'mainAddress', 'addresses'])
                ->first();

            $addressInvoice = $contact->invoiceAddress ?? $contact->mainAddress ?? $contact->addresses->first();
            $this->data['address_invoice_id'] = $addressInvoice->id;
        } elseif (! data_get($this->data, 'contact_id', false)
            && $addressInvoiceId = data_get($this->data, 'address_invoice_id', false)
        ) {
            $addressInvoice = resolve_static(Address::class, 'query')
                ->whereKey($addressInvoiceId)
                ->with('contact')
                ->first();

            $contact = $addressInvoice->contact;
            $this->data['contact_id'] = $contact->id;
        } else {
            $contact = resolve_static(Contact::class, 'query')
                ->whereKey($this->data['contact_id'] ?? null)
                ->first();
            $addressInvoice = resolve_static(Address::class, 'query')
                ->whereKey($this->data['address_invoice_id'] ?? null)
                ->first();
        }

        if ($this->data['address_delivery'] ?? false) {
            $this->data['address_delivery_id'] = data_get($this->data, 'address_delivery.id');
        } else {
            $this->data['address_delivery_id'] = $this->data['address_delivery_id']
                ?? $contact->delivery_address_id
                ?? $addressInvoice?->id;
        }

        if (is_null($contact)) {
            return;
        }

        $this->data['payment_type_id'] = $this->data['payment_type_id']
            ?? $contact->payment_type_id
            ?? PaymentType::default()?->id;
        $this->data['client_id'] ??= $contact->client_id;

        $paymentType = resolve_static(PaymentType::class, 'query')
            ->whereKey(data_get($this->data, 'payment_type_id'))
            ->first();

        $this->data['agent_id'] = $this->data['agent_id'] ?? $contact->agent_id;
        $this->data['approval_user_id'] ??= $contact->approval_user_id;
        $this->data['contact_bank_connection_id'] ??= $contact->contactBankConnections()->first()?->id;
        $this->data['payment_discount_target'] ??= $contact->discount_days
            ?? $paymentType->payment_discount_target
            ?? 0;
        $this->data['payment_discount_percent'] ??= $contact->discount_percent
            ?? $paymentType->payment_discount_percentage
            ?? 0;
        $this->data['payment_target'] ??= $contact->payment_target_days
            ?? $paymentType->payment_target
            ?? ($this->data['payment_discount_target'] ? $this->data['payment_discount_target'] + 1 : 0);
        $this->data['payment_reminder_days_1'] ??= $this->data['payment_reminder_days_1']
            ?? $contact->payment_reminder_days_1
            ?? $paymentType->payment_reminder_days_1
            ?? 1;
        $this->data['payment_reminder_days_2'] ??= $contact->payment_reminder_days_2
            ?? $paymentType->payment_reminder_days_2
            ?? 1;
        $this->data['payment_reminder_days_3'] ??= $contact->payment_reminder_days_3
            ?? $paymentType->payment_reminder_days_3
            ?? 1;

        $this->data['price_list_id'] ??= $contact->price_list_id
            ?? PriceList::default()?->id;

        $this->data['language_id'] ??= $addressInvoice->language_id
            ?? Language::default()?->id;

        $this->data['order_date'] ??= now();

        $this->rules = array_merge(
            $this->rules,
            [
                'order_type_id' => [
                    'required',
                    'integer',
                    (new ModelExists(OrderType::class))->where('client_id', $this->data['client_id']),
                ],
                'payment_type_id' => [
                    'required',
                    'integer',
                    (new ModelExists(PaymentType::class))->where('client_id', $this->data['client_id']),
                ],
            ]
        );
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(Order::class));

        $this->data = $validator->validate();

        if ($this->data['invoice_number'] ?? false) {
            $isPurchase = resolve_static(OrderType::class, 'query')
                ->whereKey($this->data['order_type_id'])
                ->whereIn('order_type_enum', [OrderTypeEnum::Purchase->value, OrderTypeEnum::PurchaseRefund->value])
                ->exists();

            if ($isPurchase && ! ($this->data['contact_id'] ?? false)) {
                throw ValidationException::withMessages([
                    'contact_id' => [__('validation.required', ['attribute' => 'contact_id'])],
                ])->errorBag('createOrder');
            }

            if (resolve_static(Order::class, 'query')
                ->where('client_id', $this->data['client_id'])
                ->where('invoice_number', $this->data['invoice_number'])
                ->when($isPurchase, fn (Builder $query) => $query->where('contact_id', $this->data['contact_id']))
                ->exists()
            ) {
                throw ValidationException::withMessages([
                    'invoice_number' => [__('validation.unique', ['attribute' => 'invoice_number'])],
                ])->errorBag('createOrder');
            }
        }
    }
}
