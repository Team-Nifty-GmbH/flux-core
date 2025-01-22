<?php

namespace FluxErp\Livewire\Portal\Shop;

use FluxErp\Actions\Comment\CreateComment;
use FluxErp\Events\PortalOrderCreated;
use FluxErp\Livewire\Forms\AddressForm;
use FluxErp\Mail\Order\OrderConfirmation;
use FluxErp\Models\Address;
use FluxErp\Models\Order;
use FluxErp\Rulesets\Address\PostalAddressRuleset;
use Illuminate\Support\Facades\Mail;
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
        $this->deliveryAddress->fill(
            auth()->user()->contact->deliveryAddress?->toArray()
            ?? auth()->user()->toArray()
        );
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
        return auth()->user()->contact->client->terms_and_conditions ?? '';
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
            $order = $this->cart()->createOrder(
                deliveryAddress: $this->deliveryAddress->toArray(),
                attributes: [
                    'commission' => $this->commission,
                ]
            );

            $this->cart()->delete();
            unset($this->cart);
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $comment = implode(
            '<br />',
            array_filter(
                [
                    $this->comment,
                    $this->delivery_date ? __('Desired delivery date') . ': ' . $this->delivery_date : null,
                ]
            )
        );

        if ($comment) {
            CreateComment::make([
                'model_type' => morph_alias(Order::class),
                'model_id' => $order->id,
                'comment' => $comment,
            ])->validate()->execute();
        }

        $this->notification()->success('Order placed successfully!');
        event(PortalOrderCreated::make($order));

        if (auth('address')->check()) {
            Mail::to(auth('address')->user())->queue(OrderConfirmation::make($order->refresh()));
        }

        $this->redirect(route('portal.checkout-finish'), true);
    }
}
