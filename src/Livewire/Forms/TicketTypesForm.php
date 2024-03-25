<?php

namespace FluxErp\Livewire\Forms;



use FluxErp\Actions\TicketType\CreateTicketType;
use FluxErp\Actions\TicketType\DeleteTicketType;
use FluxErp\Actions\TicketType\UpdateTicketType;
use FluxErp\Models\TicketType;
use Livewire\Attributes\Locked;

class TicketTypesForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?string $name = null;

    public ?string $model_type = null;

    public array $roles = [];


    public function fill($values): void
    {
        if ($values instanceof TicketType) {
            $values->loadMissing(['roles:id,name']);

            $values = $values->toArray();
        }

        parent::fill($values);
    }


    protected function getActions(): array
    {
        return [
            'create' => CreateTicketType::class,
            'update' => UpdateTicketType::class,
            'delete' => DeleteTicketType::class,
        ];
    }
}
