<?php

namespace FluxErp\Actions\Permission;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreatePermissionRequest;
use FluxErp\Models\Permission;
use Illuminate\Database\Eloquent\Model;

class CreatePermission extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreatePermissionRequest())->rules();
    }

    public static function models(): array
    {
        return [Permission::class];
    }

    public function execute(): Model
    {
        return Permission::create($this->data);
    }
}
