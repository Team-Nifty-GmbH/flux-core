<?php

namespace FluxErp\Livewire\Portal\Auth;

use FluxErp\Actions\Address\UpdateAddress;
use FluxErp\Livewire\Auth\ResetPassword as BaseResetPassword;

class ResetPassword extends BaseResetPassword
{
    protected string $passwordBroker = 'addresses';

    protected string $updateAction = UpdateAddress::class;
}
