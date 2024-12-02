<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Discount\CreateDiscount;
use FluxErp\Actions\Discount\DeleteDiscount;
use FluxErp\Actions\Discount\UpdateDiscount;
use FluxErp\Support\Livewire\Attributes\ExcludeFromActionData;
use Livewire\Attributes\Locked;

class DiscountForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    #[Locked]
    public ?string $model_type = null;

    #[Locked]
    public ?int $model_id = null;

    public ?string $name = null;

    public ?string $discount = null;

    #[ExcludeFromActionData, Locked]
    public ?string $discount_percentage = null;

    #[ExcludeFromActionData, Locked]
    public ?string $discount_currency = null;

    public ?int $order_column = null;

    public bool $is_percentage = true;

    public function getActions(): array
    {
        return [
            'create' => CreateDiscount::class,
            'update' => UpdateDiscount::class,
            'delete' => DeleteDiscount::class,
        ];
    }

    public function fill($values): void
    {
        parent::fill($values);

        $this->discount = $this->is_percentage
            ? bcmul($this->discount, 100)
            : $this->discount;
    }

    public function toActionData(): array
    {
        $data = parent::toActionData();
        $data['discount'] = $this->is_percentage
            ? bcdiv($this->discount, 100)
            : $this->discount;

        return $data;
    }
}
