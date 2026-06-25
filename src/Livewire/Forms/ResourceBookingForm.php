<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\ResourceBooking\CreateResourceBooking;
use FluxErp\Actions\ResourceBooking\DeleteResourceBooking;
use FluxErp\Actions\ResourceBooking\UpdateResourceBooking;
use FluxErp\Traits\Livewire\Form\SupportsAutoRender;
use Livewire\Attributes\Locked;

class ResourceBookingForm extends FluxForm
{
    use SupportsAutoRender;

    #[Locked]
    public ?int $id = null;

    public ?int $resource_id = null;

    public ?string $assignable_type = null;

    public ?int $assignable_id = null;

    public ?int $order_id = null;

    public ?string $start = null;

    public ?string $end = null;

    public ?string $description = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateResourceBooking::class,
            'update' => UpdateResourceBooking::class,
            'delete' => DeleteResourceBooking::class,
        ];
    }
}
