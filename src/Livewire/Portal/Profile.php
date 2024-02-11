<?php

namespace FluxErp\Livewire\Portal;

use FluxErp\Http\Requests\CreateAddressRequest;
use FluxErp\Http\Requests\UpdateAddressRequest;
use FluxErp\Models\Address;
use FluxErp\Models\Permission;
use FluxErp\Services\AddressService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Livewire\Component;
use WireUi\Traits\Actions;

class Profile extends Component
{
    use Actions;

    public array $address = [];

    public array $addresses = [];

    public string $view = 'flux::livewire.portal.profile';

    public ?string $loginPassword = null;

    public array $contactOptions = [];

    public array $permissions = [];

    public bool $showUserList = false;

    public function mount(?string $id = null): void
    {
        if ($id === null || ! auth()->user()->can('profiles.{id?}.get')) {
            $user = auth()->user();
        } elseif ($id === 'new') {
            $user = new Address();
            $user->contact_id = auth()->user()->contact_id;
            $user->client_id = auth()->user()->client_id;
            $user->company = auth()->user()->company;
            $user->language_id = null;
            $user->country_id = null;
            $user->contact_options = [];
        } else {
            $user = Address::query()->whereKey($id)->first();
            if ($user->contact_id !== auth()->user()->contact_id) {
                abort(404);
            }
        }

        $this->address = $user->load('contactOptions')->toArray();

        $this->address['permissions'] = array_map(
            'strval',
            $user->getAllPermissions()->pluck('id')->toArray()
        );

        $this->permissions = Permission::query()
            ->where('guard_name', 'address')
            ->get()
            ->toArray();

        $this->contactOptions = array_merge(
            [
                'phone' => [],
                'email' => [],
                'website' => [],
            ],
            collect($this->address['contact_options'] ?? [])->groupBy('type')->toArray()
        );
    }

    public function getRules(): array
    {
        $addressRequest = ($this->address['id'] ?? false) ? new UpdateAddressRequest() : new CreateAddressRequest();

        return Arr::prependKeysWith($addressRequest->rules(), 'address.');
    }

    public function render(): View|Factory|Application
    {
        return view($this->view, $this->addresses);
    }

    public function showUsers(): void
    {
        if (! auth()->user()->can('profiles.{id?}.get')) {
            return;
        }

        $this->addresses = Address::query()
            ->where('contact_id', auth()->user()->contact_id)
            ->get()
            ->toArray();

        $this->showUserList = true;

        $this->skipRender();
    }

    public function save(): void
    {
        $function = ($this->address['id'] ?? false) ? 'update' : 'create';

        if ($function === 'create' && ! auth()->user()->can('profiles.{id?}.get')) {
            return;
        }

        $this->address['contact_options'] = [];
        foreach ($this->contactOptions as $contactOption) {
            $this->address['contact_options'] = array_merge($this->address['contact_options'], $contactOption);
        }

        $validated = $this->validate();
        if ($function === 'update' && $this->loginPassword) {
            $validated['address']['login_password'] = $this->loginPassword;
        }

        $response = (new AddressService())->{$function}($validated['address']);

        if (auth()->user()->can('profiles.{id?}.get') && auth()->id() !== ($this->address['id'] ?? false)) {
            $address = Address::query()->whereKey($response['data']?->id ?: $response->id)->first();
            $address->syncPermissions($this->address['permissions']);
        }

        $this->notification()->success(__('Successfully saved'));
        $this->loginPassword = null;
    }
}
