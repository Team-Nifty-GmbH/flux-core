<?php

namespace FluxErp\Actions\Permission;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreatePermissionRequest;
use FluxErp\Models\Permission;
use Illuminate\Database\Eloquent\Model;

class CreatePermission extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreatePermissionRequest())->rules();
    }

    public static function models(): array
    {
        return [Permission::class];
    }

    public function performAction(): Model
    {
        return Permission::create($this->data);
    }
}
