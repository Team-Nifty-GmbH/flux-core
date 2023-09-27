<?php

namespace FluxErp\Livewire\Address;

use FluxErp\Actions\Address\CreateAddress;
use FluxErp\Actions\Address\DeleteAddress;
use FluxErp\Actions\Address\UpdateAddress;
use FluxErp\Http\Requests\CreateAddressRequest;
use FluxErp\Http\Requests\UpdateAddressRequest;
use FluxErp\Models\Address as AddressModel;
use FluxErp\Models\Contact;
use FluxErp\Models\Permission;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use WireUi\Traits\Actions;

class Address extends Component
{
    use Actions;

    public array $address;

    public array $contactOptions = [];

    public array $addressOriginal;

    public array $contact;

    public string $tab = 'address';

    public ?int $addressId = null;

    public ?string $loginPassword = null;

    public string $parentId = 'default';

    public bool $edit = false;

    public array $permissions = [];

    protected $listeners = [
        'save',
        'cancel',
        'edit',
        'duplicate',
        'addAddress',
    ];

    protected $queryString = [
        'tab' => ['except' => 'address'],
        'addressId' => ['except' => null, 'as' => 'address'],
    ];

    protected function getListeners(): array
    {
        $channel = (new Contact())->broadcastChannel() . '.' . $this->address['contact_id'];

        return array_merge(
            $this->listeners,
            ['save:' . $this->parentId => 'save'],
            [
                'echo-private:' . $channel . ',.AddressUpdated' => 'addressUpdatedEvent',
                'echo-private:' . $channel . ',.AddressDeleted' => 'addressDeletedEvent',
            ]
        );
    }

    public function mount(): void
    {
        $this->getAddress($this->addressId ?: $this->address['id'], false);

        if ($this->tab === 'permissions') {
            $this->updatedTab();
        }
    }

    protected function rules(): array
    {
        $formRequest = ($this->address['id'] ?? false) ? new UpdateAddressRequest() : new CreateAddressRequest();
        $rules = $formRequest->getRules($this->address);

        $rules = Arr::prependKeysWith($rules, 'address.');
        $rules['loginPassword'] = 'sometimes|nullable|string|min:8';

        return $rules;
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.address.address');
    }

    public function updated($propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    public function updatedAddress(): void
    {
        $this->skipRender();
    }

    public function updatedTab(): void
    {
        if ($this->tab === 'permissions') {
            $this->permissions = Permission::query()
                ->where('guard_name', 'address')
                ->get()
                ->toArray();
        }
    }

    public function edit(): void
    {
        if (! Auth::user()->can('api.addresses.put')) {
            $this->notification()->error(__('You dont have the permission to do that.'));

            return;
        }

        // TODO: Lock Model on edit
        $this->edit = true;
        $this->skipRender();
    }

    public function save(int $contactId = null): ?array
    {
        $function = ($this->address['id'] ?? false)
            ? new UpdateAddress([])
            : new CreateAddress([]);

        $this->address['contact_options'] = [];
        foreach ($this->contactOptions as $contactOption) {
            $this->address['contact_options'] = array_merge($this->address['contact_options'], $contactOption);
        }

        $this->address['contact_id'] = $contactId ?? $this->address['contact_id'];
        if (($this->address['id'] ?? false) && $this->loginPassword) {
            $this->address['login_password'] = $this->loginPassword;
        }

        $this->address['can_login'] = ($this->address['login_name'] ?? false) && ($this->address['can_login'] ?? false)
            ? $this->address['can_login']
            : false;
        $function->setData($this->address);

        $this->loginPassword = '';
        try {
            $model = $function->validate()->checkPermission()->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return null;
        }

        $model->syncPermissions($this->address['permissions'] ?? []);

        // TODO: remove all locks
        $this->notification()->success(__('Address saved'));
        $this->edit = false;
        $this->getAddress($model->id);

        $this->addressOriginal = $model->toArray();
        $this->skipRender();
        $model->append('name');

        return $model->toArray();
    }

    public function cancel(): void
    {
        $this->loginPassword = '';

        $this->getAddress(($this->address['id'] ?? false) ?: $this->addresses[0]['id']);

        $this->edit = false;

        $this->skipRender();
        // TODO: remove all locks
    }

    public function delete(): ?array
    {
        if (! Auth::user()->can('api.addresses.{id}.delete')) {
            $this->notification()->error(__('You dont have the permission to do that.'));

            return null;
        }

        if ($this->address['is_main_address']) {
            $this->notification()->error(__('You cant delete the main address.'));

            return null;
        }

        try {
            DeleteAddress::make($this->address)->validate()->execute();
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);

            return null;
        }

        $this->notification()->success(__('Address deleted'));
        $this->edit = false;

        $this->skipRender();

        return $this->address;
    }

    public function duplicate(): void
    {
        $this->addressOriginal = $this->address;
        $address = $this->address;
        unset($address['id'], $address['uuid']);

        $address['is_main_address'] = false;
        $this->address = $address;
        $this->edit = true;

        $this->skipRender();
    }

    public function addAddress(): void
    {
        $this->addressOriginal = $this->address;
        $this->getAddress();

        $this->edit = true;

        $this->skipRender();
    }

    public function addressUpdatedEvent(array $data): void
    {
        $this->address = $data['model'];
    }

    public function addressDeletedEvent(array $data): void
    {
        if ($data['model']['id'] === $this->addressId) {
            $nextAddress = AddressModel::query()
                ->where('contact_id', $this->address['contact_id'])
                ->first();

            if ($nextAddress) {
                $this->address = $nextAddress->toArray();
                $this->addressId = $nextAddress->id;
            }
        }
    }

    public function getAddress(int $addressId = null, bool $skipRender = true): void
    {
        if ($addressId) {
            $address = AddressModel::query()
                ->whereKey($addressId)
                ->with('contactOptions', fn (HasMany $builder) => $builder->orderBy('type'))
                ->first();
        } else {
            $address = new AddressModel(
                [
                    'contact_id' => $this->contact['id'],
                    'client_id' => $this->contact['client_id'],
                    'is_main_address' => false,
                    'contact_options' => [],
                ]);
        }

        $this->address = $address->toArray();

        $this->contactOptions = array_merge(
            [
                'phone' => [],
                'email' => [],
                'website' => [],
            ],
            collect($this->address['contact_options'] ?? [])->groupBy('type')->toArray()
        );

        $this->address['permissions'] = array_map(
            'strval',
            $address->getAllPermissions()->pluck('id')->toArray()
        );
        $this->addressId = $address->id;

        if ($skipRender) {
            $this->skipRender();
        }
    }
}
