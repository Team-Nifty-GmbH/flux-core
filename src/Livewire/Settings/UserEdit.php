<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Permission\UpdateUserPermissions;
use FluxErp\Actions\Role\UpdateUserRoles;
use FluxErp\Actions\User\CreateUser;
use FluxErp\Actions\User\UpdateUser;
use FluxErp\Actions\User\UpdateUserClients;
use FluxErp\Livewire\Forms\UserForm;
use FluxErp\Models\Client;
use FluxErp\Models\Language;
use FluxErp\Models\MailAccount;
use FluxErp\Models\Permission;
use FluxErp\Models\Role;
use FluxErp\Models\User;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Exceptions\UnauthorizedException;

class UserEdit extends Component
{
    use Actions, WithPagination;

    public UserForm $userForm;

    public string $searchPermission = '';

    public array $lockedPermissions = [];

    public bool $isSuperAdmin = false;

    public function mount(User $user): void
    {
        $user->loadMissing(['roles', 'mailAccounts:id', 'clients:id']);
        $this->userForm->fill($user);
        $this->isSuperAdmin = $user->hasRole('Super Admin');

        $this->userForm->permissions = $user
            ->getDirectPermissions()
            ->pluck(['id'])
            ->toArray();
        $this->userForm->roles = $user->roles->pluck('id')->toArray();
        $this->userForm->mail_accounts = $user->mailAccounts->pluck('id')->toArray();
        $this->userForm->clients = $user->clients->pluck('id')->toArray();

        $this->updatedUserRoles();
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
                'roles' => resolve_static(Role::class, 'query')
                    ->whereIn('guard_name', resolve_static(User::class, 'guardNames'))
                    ->get(['id', 'name', 'guard_name'])
                    ->toArray(),
                'mailAccounts' => resolve_static(MailAccount::class, 'query')
                    ->get(['id', 'email'])
                    ->toArray(),
                'users' => resolve_static(User::class, 'query')
                    ->where('is_active', true)
                    ->get(['id', 'email', 'firstname', 'lastname', 'name'])
                    ->toArray(),
                'languages' => resolve_static(Language::class, 'query')
                    ->get(['id', 'name'])
                    ->toArray(),
            ]
        );
    }

    #[Renderless]
    public function save(): void
    {
        try {
            if (
                in_array(
                    resolve_static(Role::class, 'findByName', ['name' => 'Super Admin'])->id,
                    $this->userForm->roles
                )
                && ! auth()->user()->hasRole('Super Admin')
            ) {
                return;
            }
        } catch (RoleDoesNotExist) {
        }

        $action = ($this->userForm->id ?? false) ? UpdateUser::class : CreateUser::class;

        try {
            $user = $action::make($this->userForm)
                ->checkPermission()
                ->setRulesFromRulesets()
                ->addRules(['password' => 'confirmed'])
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->notification()->success(__(':model saved', ['model' => __('User')]));

        try {
            UpdateUserPermissions::make([
                'user_id' => $user['id'],
                'permissions' => array_map('intval', $this->userForm->permissions ?? []),
                'sync' => true,
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);
        }

        try {
            UpdateUserRoles::make([
                'user_id' => $user['id'],
                'roles' => array_map('intval', $this->userForm->roles),
                'sync' => true,
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);
        }

        try {
            UpdateUserClients::make([
                'user_id' => $user['id'],
                'clients' => $this->userForm->clients,
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);
        }

        $user->loadMissing(['roles', 'permissions', 'clients:id']);
        $this->userForm->fill($user);
    }

    #[Renderless]
    public function delete(): void
    {
        try {
            $this->userForm->delete();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->redirectRoute('settings.users', navigate: true);
    }

    #[Renderless]
    public function cancel(): void
    {
        $this->redirectRoute('settings.users', navigate: true);
    }

    #[Renderless]
    public function updatedUserRoles(): void
    {
        try {
            if (in_array(
                resolve_static(Role::class, 'findByName', ['name' => 'Super Admin'])->id,
                $this->userForm->roles
            )) {
                $this->lockedPermissions = resolve_static(Permission::class, 'query')
                    ->pluck('id')
                    ->toArray();
                $this->isSuperAdmin = true;

                return;
            }
        } catch (RoleDoesNotExist) {
            $this->lockedPermissions = [];
            $this->isSuperAdmin = false;
        }

        $lockedPermissions = resolve_static(Role::class, 'query')
            ->whereIntegerInRaw('id', $this->userForm->roles)
            ->with('permissions')
            ->get()
            ->pluck('permissions.*.id')
            ->toArray();

        $this->lockedPermissions = Arr::flatten($lockedPermissions);
    }
}
