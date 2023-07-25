<?php

namespace FluxErp\Http\Livewire\Settings;

use FluxErp\Http\Requests\CreateUserRequest;
use FluxErp\Http\Requests\UpdateUserRequest;
use FluxErp\Models\Language;
use FluxErp\Models\Permission;
use FluxErp\Models\Role;
use FluxErp\Models\User;
use FluxErp\Services\UserService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Livewire\Component;
use Livewire\WithPagination;
use WireUi\Traits\Actions;

class UserEdit extends Component
{
    use Actions, WithPagination;

    public array $user = [];

    public array $lockedPermissions = [];

    public string $searchPermission = '';

    public array $languages = [];

    public array $roles = [];

    public array $guardNames = [];

    public bool $isSuperAdmin = false;

    protected $listeners = [
        'show',
        'save',
        'delete',
    ];

    public function boot(): void
    {
        $this->user = array_fill_keys(
            array_keys((new CreateUserRequest())->rules()),
            null
        );
        $this->languages = Language::all(['id', 'name'])->toArray();

        // get guards for users
        $providers = config('auth.providers');
        $providerNames = [];
        foreach ($providers as $name => $provider) {
            if ($provider['driver'] === 'eloquent' && $provider['model'] === User::class) {
                $providerNames[] = $name;
            }
        }

        $this->guardNames = array_keys(
            collect(config('auth.guards'))
                ->whereIn('provider', $providerNames)
                ->toArray()
        );

        $this->roles = Role::query()->whereIn('guard_name', $this->guardNames)
            ->get(['id', 'name', 'guard_name'])
            ->toArray();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.settings.user-edit',
            [
                'permissions' => Permission::search($this->searchPermission)
                    ->whereIn('guard_name', $this->guardNames)
                    ->paginate(pageName: 'permissionsPage'),
            ]
        );
    }

    public function getRules(): array
    {
        $request = ($this->user['id'] ?? false) ? new UpdateUserRequest() : new CreateUserRequest();
        $rules = $request->getRules($this->user);

        $rules['password'][] = 'confirmed';

        if ($request instanceof UpdateUserRequest && is_string($rules['email'])) {
            $rules['email'] .= ',' . $this->user['id'];
        }

        return Arr::prependKeysWith($rules, 'user.');
    }

    public function show(?int $id = null): void
    {
        $user = User::query()->whereKey($id)->with(['roles'])->firstOrNew();

        $this->resetErrorBag();

        if ($user->is_locked) {
            $this->notification()->error(__('Record locked.'));
            $this->skipRender();

            return;
        }

        if ($user->exists) {
            $user?->lock()->updateOrCreate([]);
        }

        $this->user = $user->toArray();
        $this->isSuperAdmin = $user->hasRole('Super Admin');

        $this->user['permissions'] = $user
            ->getDirectPermissions()
            ->pluck(['id'])
            ->toArray();
        $this->user['roles'] = $user->roles()->get(['id'])->pluck(['id'])->toArray();

        $this->updatedUserRoles();
        $this->skipRender();
    }

    public function save(): void
    {
        if (
            ! user_can(['api.users.{id}.put', 'api.users.post']) ||
            // Only a Super Admin can set a new Super Admin.
            (in_array(Role::findByName('Super Admin')->id, $this->user['roles']) &&
                ! auth()->user()->hasRole('Super Admin')
            )
        ) {
            return;
        }

        $this->validate();

        $service = new UserService();
        $response = ($this->user['id'] ?? false) ? $service->update($this->user) : $service->create($this->user);

        if ($response instanceof Model || $response['status'] < 300) {
            $this->notification()->success(__('User saved successful.'));

            $user = $response instanceof Model ? $response : User::query()->whereKey($this->user['id'])->first();
        } else {
            $this->notification()->error(
                implode(',', array_keys($response['errors'])),
                implode(', ', Arr::dot($response['errors']))
            );

            return;
        }

        if (user_can('api.permissions.give.put')) {
            $permissions = Permission::query()->whereIntegerInRaw('id', array_map('intval', $this->user['permissions']))->get();
            $user->syncPermissions($permissions);
        }

        if (user_can('api.roles.give.put')) {
            // We have to pass Role instances because spatie checks for the guard which HAS to be the one
            // the frontend user is using currently.
            $roles = Role::query()->whereIntegerInRaw('id', array_map('intval', $this->user['roles']))->get();
            $user->syncRoles($roles);
        }

        $this->skipRender();
        $this->emitUp('closeModal');
        $this->emitTo('data-tables.user-list', 'loadData');
    }

    public function delete(): void
    {
        if (! user_can('api.users.{id}.delete')) {
            return;
        }

        (new UserService())->delete($this->user['id']);

        $this->skipRender();
        $this->emitUp('closeModal');
    }

    public function updatedUserRoles(): void
    {
        $this->skipRender();

        if (in_array(Role::findByName('Super Admin')->id, $this->user['roles'])) {
            $this->lockedPermissions = Permission::all(['id'])->pluck('id')->toArray();
            $this->isSuperAdmin = true;

            return;
        }

        $lockedPermissions = Role::query()
            ->whereIntegerInRaw('id', $this->user['roles'])
            ->with('permissions')
            ->get()
            ->pluck('permissions.*.id')
            ->toArray();

        $this->lockedPermissions = Arr::flatten($lockedPermissions);
    }
}
