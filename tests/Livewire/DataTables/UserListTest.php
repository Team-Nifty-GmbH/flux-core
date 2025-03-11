<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\UserList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class UserListTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(UserList::class)
            ->assertStatus(200);
    }
}
