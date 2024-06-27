<?php

namespace FluxErp\Livewire\Portal\Shop;

use FluxErp\Actions\Comment\CreateComment;
use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Livewire\Forms\AddressForm;
use FluxErp\Models\Address;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Rulesets\Address\PostalAddressRuleset;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Rule;

class Checkout extends Cart
{
    public ?string $comment = null;

    public ?string $delivery_date = null;

    public ?string $commission = null;

    public AddressForm $address;

    public AddressForm $deliveryAddress;

    public bool $edit = true;

    #[Rule('accepted')]
    public bool $termsAndConditions = false;

    public function mount(): void
    {
        $this->deliveryAddress->fill(auth()->user()->contact->deliveryAddress->toArray());
    }

    public function render(): View
    {
        return view('flux::livewire.portal.shop.checkout');
    }

    #[Renderless]
    public function loadAddress(Address $address): void
    {
        $this->address->reset();
        $this->address->fill($address->toArray());
        $this->edit = ! $address->id;
    }

    #[Renderless]
    public function loadTermsAndConditions(): string
    {
        return auth()->user()->contact->client->terms_and_conditions;
    }

    public function saveDeliveryAddress(): bool
    {
        try {
            $this->address->validate(resolve_static(PostalAddressRuleset::class, 'getRules'));
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->deliveryAddress->fill($this->address->toArray());

        return true;
    }

    public function buy(): void
    {
        try {
            $this->validateOnly('termsAndConditions');
            $order = CreateOrder::make([
                'order_type_id' => OrderType::query()
                    ->where('order_type_enum', 'order')
                    ->first()
                    ->id,
                'contact_id' => auth()->user()->contact->id,
                'client_id' => auth()->user()->contact->client->id,
                'is_imported' => true,
                'commission' => $this->commission,
                'address_delivery' => $this->deliveryAddress->toArray(),
            ])
                ->validate()
                ->execute();

            foreach ($this->cart()->cartItems as $cartItem) {
                CreateOrderPosition::make([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'amount' => $cartItem->amount,
                    'unit_price' => $cartItem->price,
                ])
                    ->validate()
                    ->execute();
            }

            $order->calculatePrices()->save();

            $this->cart()->delete();
            unset($this->cart);
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        if ($this->comment) {
            CreateComment::make([
                'model_type' => morph_alias(Order::class),
                'model_id' => $order->id,
                'comment' => $this->comment,
            ])->validate()->execute();
        }

        $this->notification()->success('Order placed successfully!');
        $this->redirect(route('portal.checkout-finish'), true);
    }
}
