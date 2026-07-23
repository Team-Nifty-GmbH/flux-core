<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Resource\CreateResource;
use FluxErp\Actions\Resource\DeleteResource;
use FluxErp\Actions\Resource\UpdateResource;
use FluxErp\Traits\Livewire\Form\SupportsAutoRender;
use Livewire\Attributes\Locked;

class ResourceForm extends FluxForm
{
    use SupportsAutoRender;

    public bool $allow_overbooking = false;

    public ?string $description = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public ?string $name = null;

    public ?int $product_id = null;

    public ?string $resource_number = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateResource::class,
            'update' => UpdateResource::class,
            'delete' => DeleteResource::class,
        ];
    }
}
