<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\Forms\OrderAddressesForm;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;
use WireUi\Traits\Actions;

#[Lazy]
class AdditionalAddresses extends Component
{
    use Actions;

    public int $orderId;

    public int $clientId;

    public OrderAddressesForm $form;

    public ?int $address_id = null;

    public ?int $address_type_id = null;

    public function render(): View
    {
        $this->form->id = $this->orderId;
        $this->form->addresses = resolve_static(\FluxErp\Models\Order::class, 'query')
            ->whereKey($this->orderId)
            ->with('addresses')
            ->first(['id'])
            ->addresses
            ->map(fn ($address) => [
                'address_type' => $address->pivot->addressType->name,
                'address_id' => $address->id,
                'address' => $address->postal_address,
                'address_type_id' => $address->pivot->address_type_id,
            ])
            ->toArray();

        return view('flux::livewire.order.additional-addresses');
    }

    public function placeholder(): View
    {
        return view('flux::livewire.placeholders.box');
    }

    public function delete(int $id): void
    {
        $current = $this->form->addresses;
        $this->form->addresses = array_filter(
            $this->form->addresses,
            fn (array $address) => $address['address_id'] !== $id
        );

        try {
            $this->form->save();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);
            $this->form->addresses = $current;

            return;
        }
    }

    public function save(): bool
    {
        $current = $this->form->addresses;
        $this->form->addresses[] = [
            'address_id' => $this->address_id,
            'address_type_id' => $this->address_type_id,
        ];

        try {
            $this->form->save();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);
            $this->form->addresses = $current;

            return false;
        }

        $this->address_id = null;
        $this->address_type_id = null;

        return true;
    }
}
