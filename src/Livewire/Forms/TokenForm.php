<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Token\CreateToken;
use FluxErp\Actions\Token\DeleteToken;
use FluxErp\Models\Permission;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Livewire\Attributes\Locked;

class TokenForm extends FluxForm
{
    use SupportsAutoRender;

    public ?array $abilities = null;

    public ?string $description = null;

    #[Locked]
    public ?int $id = null;

    public ?string $name = null;

    public array $permissions = [];

    #[Locked]
    public ?string $plain_text_token = null;

    public function toActionData(): array
    {
        $actionData = parent::toActionData();

        $actionData['permissions'] = resolve_static(Permission::class, 'query')
            ->whereIn('name', $this->permissions)
            ->where('guard_name', 'token')
            ->pluck('id')
            ->toArray();

        return $actionData;
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateToken::class,
            'delete' => DeleteToken::class,
        ];
    }
}
