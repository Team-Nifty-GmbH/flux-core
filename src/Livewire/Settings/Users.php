<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\User\CreateUser;
use FluxErp\Actions\User\UpdateUser;
use FluxErp\Livewire\DataTables\UserList;
use FluxErp\Livewire\Forms\UserForm;
use FluxErp\Models\User;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Users extends UserList
{
    public UserForm $userForm;

    protected ?string $includeBefore = 'flux::livewire.settings.users';

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Create'))
                ->color('indigo')
                ->icon('plus')
                ->when(resolve_static(CreateUser::class, 'canPerformAction', [false]))
                ->wireClick('edit()'),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->color('indigo')
                ->icon('pencil')
                ->when(resolve_static(UpdateUser::class, 'canPerformAction', [false]))
                ->wireClick('edit(record.id)'),
        ];
    }

    #[Renderless]
    public function edit(User $user): void
    {
        if ($user->exists) {
            $this->redirectRoute('settings.users.edit', ['user' => $user->getKey()], navigate: true);

            return;
        }

        $this->userForm->reset();
        $this->js(<<<'JS'
            $modalOpen('create-user-modal');
        JS);
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->userForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();
        $this->edit($this->userForm->getActionResult());

        return true;
    }
}
