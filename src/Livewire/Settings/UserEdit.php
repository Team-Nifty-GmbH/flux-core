<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Permission\UpdateUserPermissions;
use FluxErp\Actions\Role\UpdateUserRoles;
use FluxErp\Actions\User\CreateUser;
use FluxErp\Actions\User\DeleteUser;
use FluxErp\Actions\User\UpdateUser;
use FluxErp\Actions\User\UpdateUserClients;
use FluxErp\Models\Client;
use FluxErp\Models\Language;
use FluxErp\Models\MailAccount;
use FluxErp\Models\Permission;
use FluxErp\Models\Role;
use FluxErp\Models\User;
use FluxErp\Rulesets\User\CreateUserRuleset;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class UserEdit extends Component
{
    use Actions, WithPagination;

    public array $user = [];

    public array $lockedPermissions = [];

    public string $searchPermission = '';

    public array $languages = [];

    public array $roles = [];

    public array $mailAccounts = [];

    public array $users = [];

    public bool $isSuperAdmin = false;

    protected $listeners = [
        'show',
        'save',
        'delete',
    ];

    public function mount(): void
    {
        $this->user = array_fill_keys(
            array_keys(resolve_static(CreateUserRuleset::class, 'getRules')),
            null
        );
        $this->languages = app(Language::class)->all(['id', 'name'])->toArray();

        $this->roles = resolve_static(Role::class, 'query')
            ->whereIn('guard_name', resolve_static(User::class, 'guardNames'))
            ->get(['id', 'name', 'guard_name'])
            ->toArray();

        $this->mailAccounts = resolve_static(MailAccount::class, 'query')
            ->get(['id', 'email'])
            ->toArray();

        $this->users = resolve_static(User::class, 'query')
            ->get(['id', 'email', 'firstname', 'lastname', 'name'])
            ->toArray();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.settings.user-edit',
            [
                'permissions' => resolve_static(Permission::class, 'query')
                    ->where('guard_name', '!=', 'address')
                    ->when($this->searchPermission, fn ($query) => $query->search($this->searchPermission))
                    ->paginate(pageName: 'permissionsPage'),
                'clients' => resolve_static(Client::class, 'query')
                    ->get(['id', 'name', 'client_code']),
            ]
        );
    }

    public function show(?int $id = null): void
    {
        $user = resolve_static(User::class, 'query')
            ->whereKey($id)
            ->with(['roles', 'mailAccounts:id', 'clients:id'])
            ->firstOrNew();

        $this->resetErrorBag();

        $this->user = $user->toArray();
        $this->isSuperAdmin = $user->hasRole('Super Admin');

        $this->user['permissions'] = $user
            ->getDirectPermissions()
            ->pluck(['id'])
            ->toArray();
        $this->user['roles'] = $user->roles->pluck('id')->toArray();
        $this->user['mail_accounts'] = $user->mailAccounts->pluck('id')->toArray();
        $this->user['clients'] = $user->clients->pluck('id')->toArray();

        $this->updatedUserRoles();
        $this->skipRender();
    }

    public function save(): void
    {
        try {
            if (
                in_array(
                    resolve_static(Role::class, 'findByName', ['name' => 'Super Admin'])->id,
                    $this->user['roles']
                )
                && ! auth()->user()->hasRole('Super Admin')
            ) {
                return;
            }
        } catch (RoleDoesNotExist) {
        }

        $action = ($this->user['id'] ?? false) ? UpdateUser::class : CreateUser::class;

        try {
            $user = $action::make($this->user)
                ->checkPermission()
                ->setRulesFromRulesets()
                ->addRules(['password' => 'confirmed'])
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->notification()->success(__('User saved successful.'));
        $this->dispatch('closeModal');
        $this->dispatch('loadData')->to('data-tables.user-list');

        try {
            UpdateUserPermissions::make([
                'user_id' => $user['id'],
                'permissions' => array_map('intval', $this->user['permissions'] ?? []),
                'sync' => true,
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);
        }

        try {
            UpdateUserRoles::make([
                'user_id' => $user['id'],
                'roles' => array_map('intval', $this->user['roles']),
                'sync' => true,
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);
        }

        try {
            UpdateUserClients::make([
                'user_id' => $user['id'],
                'clients' => $this->user['clients'],
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);
        }

        $this->user = $user->load(['roles', 'permissions', 'clients:id'])->toArray();
    }

    public function delete(): bool
    {
        $this->skipRender();

        try {
            DeleteUser::make($this->user)
                ->checkPermission()
                ->validate()
                ->execute();

            $this->skipRender();
            $this->dispatch('closeModal');
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->dispatch('loadData')->to('data-tables.user-list');

        return true;
    }

    public function updatedUserRoles(): void
    {
        $this->skipRender();

        try {
            if (in_array(
                resolve_static(Role::class, 'findByName', ['name' => 'Super Admin'])->id,
                $this->user['roles']
            )) {
                $this->lockedPermissions = app(Permission::class)->all(['id'])->pluck('id')->toArray();
                $this->isSuperAdmin = true;

                return;
            }
        } catch (RoleDoesNotExist) {
            $this->lockedPermissions = [];
            $this->isSuperAdmin = false;
        }

        $lockedPermissions = resolve_static(Role::class, 'query')
            ->whereIntegerInRaw('id', $this->user['roles'])
            ->with('permissions')
            ->get()
            ->pluck('permissions.*.id')
            ->toArray();

        $this->lockedPermissions = Arr::flatten($lockedPermissions);
    }
}
