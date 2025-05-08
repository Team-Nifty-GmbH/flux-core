<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\CommissionRate\CreateCommissionRate;
use FluxErp\Actions\CommissionRate\DeleteCommissionRate;
use FluxErp\Actions\CommissionRate\UpdateCommissionRate;

class CommissionRateForm extends FluxForm
{
    public ?int $category_id = null;

    public float|string|null $commission_rate = null;

    public ?int $contact_id = null;

    public ?int $id = null;

    public ?int $product_id = null;

    public ?int $user_id = null;

    public function fill($values): void
    {
        parent::fill($values);

        $this->commission_rate = bcmul($this->commission_rate, '100');
    }

    public function modalName(): string
    {
        return 'edit-commission-rate-modal';
    }

    public function toActionData(): array
    {
        $data = parent::toActionData();

        $data['commission_rate'] = bcdiv($this->commission_rate, '100');

        return $data;
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateCommissionRate::class,
            'update' => UpdateCommissionRate::class,
            'delete' => DeleteCommissionRate::class,
        ];
    }
}
