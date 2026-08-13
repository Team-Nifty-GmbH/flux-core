<?php

namespace FluxErp\Livewire\Contact;

use FluxErp\Actions\Address\DetachAddress;
use FluxErp\Actions\Address\MoveAddress;
use FluxErp\Actions\Tag\CreateTag;
use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\Forms\AddressForm;
use FluxErp\Livewire\Forms\ContactForm;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\States\Address\AdvertisingState;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class Addresses extends Component
{
    use Actions, WithTabs;

    public AddressForm $address;

    public array $addresses = [];

    #[Url(as: 'address', except: null)]
    public int|string|null $addressId = null;

    public array $availableStates = [];

    #[Modelable]
    public ContactForm $contact;

    public bool $edit = false;

    public ?int $moveToContactId = null;

    public string $tab = 'address.address';

    public function mount(): void
    {
        $this->availableStates = app(Address::class)
            ->getStatesFor('advertising_state')
            ->map(function (string $state) {
                return [
                    'label' => __(Str::headline($state)),
                    'name' => $state,
                ];
            })
            ->toArray();

        $this->loadAddresses();

        $this->address->fill(
            $this->addressId
                ? resolve_static(Address::class, 'query')
                    ->whereKey($this->addressId)
                    ->with(['contactOptions', 'tags:id'])
                    ->first()
                    ?? $this->contact->main_address
                : $this->contact->main_address
        );

        $this->addressId = $this->address->id;
    }

    public function render(): Application|Factory|View
    {
        return view('flux::livewire.contact.addresses');
    }

    #[Renderless]
    public function addressDeleted(array $params): void
    {
        $model = $params['model'];

        $this->loadAddresses();

        if ($model['id'] === $this->address->id) {
            $this->address->reset('id');
            $this->reloadAddress();
        }
    }

    #[Renderless]
    public function addressUpdated(array $params): void
    {
        $model = $params['model'];

        if ($model['id'] === $this->address->id) {
            $this->reloadAddress();

            return;
        }

        $this->loadAddresses();
    }

    #[Renderless]
    public function addTag(string $name): void
    {
        try {
            $tag = CreateTag::make([
                'name' => $name,
                'type' => morph_alias(Address::class),
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->address->tags[] = $tag->id;
        $this->js(<<<'JS'
            edit = true;
        JS);
    }

    #[Renderless]
    public function delete(): void
    {
        try {
            $this->address->delete();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->addresses = array_values(array_filter(
            $this->addresses,
            fn ($address) => $address['id'] !== $this->addressId
        ));

        $this->selectFirstAddress();

        $this->edit = false;
    }

    #[Renderless]
    public function detach(): void
    {
        try {
            $address = DetachAddress::make(['id' => $this->addressId])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->redirect($address->contact->getUrl(), true);
    }

    #[Renderless]
    public function evaluate(): void {}

    #[Renderless]
    public function getListeners(): array
    {
        $model = app(Address::class);

        $listeners = [];
        foreach ($this->addresses as $address) {
            $model->id = $address['id'];
            $channel = 'echo-private:' . $model->broadcastChannel();
            $listeners[$channel . ',.AddressUpdated'] = 'addressUpdated';
            $listeners[$channel . ',.AddressDeleted'] = 'addressDeleted';
        }

        $contactModel = app(Contact::class);
        $contactModel->id = $this->contact->id;
        $listeners['echo-private:' . $contactModel->broadcastChannel() . ',.AddressCreated'] = 'loadAddresses';

        return $listeners;
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('address.address')
                ->text(__('Address')),
            TabButton::make('address.comments')
                ->text(__('Comments'))
                ->attributes([
                    'x-cloak',
                    'x-show' => '$wire.address.id',
                ])
                ->isLivewireComponent()
                ->wireModel('address.id'),
            TabButton::make('address.attachments')
                ->text(__('Attachments'))
                ->attributes([
                    'x-cloak',
                    'x-show' => '$wire.address.id',
                ])
                ->isLivewireComponent()
                ->wireModel('address.id'),
            TabButton::make('address.communication')
                ->text(__('Communication'))
                ->attributes([
                    'x-cloak',
                    'x-show' => '$wire.address.id',
                ])
                ->isLivewireComponent()
                ->wireModel('address.id'),
            TabButton::make('address.tasks')
                ->text(__('Tasks'))
                ->attributes([
                    'x-cloak',
                    'x-show' => '$wire.address.id',
                ])
                ->isLivewireComponent()
                ->wireModel('address.id'),
            TabButton::make('address.activities')
                ->text(__('Activities'))
                ->attributes([
                    'x-cloak',
                    'x-show' => '$wire.address.id',
                ])
                ->isLivewireComponent()
                ->wireModel('address.id'),
        ];
    }

    public function loadAddresses(): void
    {
        $addresses = resolve_static(Address::class, 'query')
            ->where('contact_id', $this->contact->id)
            ->orderByDesc('is_main_address')
            ->orderByDesc('is_invoice_address')
            ->orderByDesc('is_delivery_address')
            ->orderByDesc('is_active')
            ->get()
            ->each(fn (Address $address) => $address->append('postal_address'));

        foreach ($addresses as $address) {
            $this->listeners[
                'echo-private:' . $address->broadcastChannel(false) . ',.AddressUpdated'
            ] = 'addressUpdated';
            $this->listeners[
                'echo-private:' . $address->broadcastChannel(false) . ',.AddressDeleted'
            ] = 'addressDeleted';
        }

        $this->addresses = $addresses->toArray();
    }

    #[Renderless]
    public function move(): void
    {
        try {
            $address = MoveAddress::make([
                'id' => $this->addressId,
                'contact_id' => $this->moveToContactId,
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->reset('moveToContactId');

        $this->redirect($address->contact->getUrl(), true);
    }

    #[Renderless]
    public function new(): void
    {
        $this->resetErrorBag();
        $this->address->reset();

        $this->address->contact_id = $this->contact->id;
        $this->address->advertising_state = resolve_static(AdvertisingState::class, 'config')
            ->defaultStateClass::getMorphClass();
        $this->addressId = null;
        $this->edit = true;
    }

    #[Renderless]
    public function reloadAddress(): void
    {
        if (! $this->address->id) {
            $this->selectFirstAddress();

            return;
        }

        $address = resolve_static(Address::class, 'query')
            ->whereKey($this->address->id)
            ->with('contactOptions')
            ->first();

        $this->addressId = $this->address->id;

        $this->address->reset();
        $this->address->fill($address);
    }

    public function replicate(): void
    {
        $this->tab = 'address.address';
        $this->resetErrorBag();
        $this->address->reset(
            'id',
            'email',
            'is_main_address',
            'is_delivery_address',
            'is_invoice_address',
            'can_login',
        );

        $this->address->advertising_state = resolve_static(AdvertisingState::class, 'config')
            ->defaultStateClass::getMorphClass();

        $this->addressId = null;
        $this->edit = true;
    }

    #[Renderless]
    public function save(): void
    {
        $isNew = ! $this->addressId;
        try {
            $this->address->save();

            $result = $this->address->getActionResult();
            $result->loadMissing('contactOptions');
            $this->address->fill($result);
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        if ($isNew) {
            $this->addressId = $this->address->id;
        }

        $this->loadAddresses();

        $this->edit = false;
    }

    public function select(Address $address): void
    {
        $address->loadMissing(['contactOptions', 'tags:id']);

        $currentTab = $this->getTabButton($this->tab);
        if (! $currentTab->isLivewireComponent) {
            $this->skipRender();
        }

        $this->resetErrorBag();
        $this->address->reset();
        $this->address->fill($address);

        $this->addressId = $this->address->id;
    }

    /**
     * Whichever address is first takes over the detail pane once the shown one
     * is gone. There is not always one: deleting the contact deletes all of its
     * addresses, and the broadcast for each of them still arrives. Reading the
     * first of none demanded a row that is gone, and where a stale entry was
     * still listed the selection demanded an address the query had not found.
     */
    private function selectFirstAddress(): void
    {
        $address = resolve_static(Address::class, 'query')
            ->whereKey(data_get($this->addresses, '0.id'))
            ->with('contactOptions')
            ->first();

        if ($address) {
            $this->select($address);
        }
    }
}
