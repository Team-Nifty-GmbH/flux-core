<?php

namespace FluxErp\Actions\Permission;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreatePermissionRequest;
use FluxErp\Models\Permission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class CreatePermission implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreatePermissionRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'permission.create';
    }

    public static function description(): string|null
    {
        return 'create permission';
    }

    public static function models(): array
    {
        return [Permission::class];
    }

    public function execute(): Model
    {
        return Permission::create($this->data);
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }
}
