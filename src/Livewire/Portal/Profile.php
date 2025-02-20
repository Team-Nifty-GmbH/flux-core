<?php

namespace FluxErp\Livewire\Portal;

use FluxErp\Actions\Address\CreateAddress;
use FluxErp\Actions\Address\UpdateAddress;
use FluxErp\Models\Address;
use FluxErp\Models\Permission;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

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
            $user = app(Address::class);
            $user->contact_id = auth()->user()->contact_id;
            $user->client_id = auth()->user()->client_id;
            $user->company = auth()->user()->company;
            $user->language_id = null;
            $user->country_id = null;
            $user->contact_options = [];
        } else {
            $user = resolve_static(Address::class, 'query')
                ->whereKey($id)
                ->first();

            if ($user?->contact_id !== auth()->user()->contact_id) {
                abort(404);
            }
        }

        $this->address = $user->load('contactOptions')->toArray();

        $this->address['permissions'] = $user->getAllPermissions()->pluck('id')->toArray();

        $this->permissions = resolve_static(Permission::class, 'query')
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

    public function render(): View|Factory|Application
    {
        return view($this->view, $this->addresses);
    }

    public function showUsers(): void
    {
        if (! auth()->user()->can('profiles.{id?}.get')) {
            return;
        }

        $this->addresses = resolve_static(Address::class, 'query')
            ->where('contact_id', auth()->user()->contact_id)
            ->get()
            ->toArray();

        $this->showUserList = true;

        $this->skipRender();
    }

    public function save(): void
    {
        $action = $addressId = data_get($this->address, 'id') ? UpdateAddress::class : CreateAddress::class;

        if ($action === CreateAddress::class && ! auth()->user()->can('profiles.{id?}.get')) {
            return;
        }

        $this->address['contact_options'] = [];
        foreach ($this->contactOptions as $contactOption) {
            $this->address['contact_options'] = array_merge($this->address['contact_options'], $contactOption);
        }

        if ($action === UpdateAddress::class && $this->loginPassword) {
            $this->address['password'] = $this->loginPassword;
        }

        if (! auth()->user()->can('profiles.{id?}.get') || auth()->id() === $addressId) {
            unset($this->address['permissions']);
        }

        try {
            $action::make($this->address)
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->notification()->success(__(':model saved', ['model' => __('My Profile')]))->send();
        $this->loginPassword = null;
    }
}
