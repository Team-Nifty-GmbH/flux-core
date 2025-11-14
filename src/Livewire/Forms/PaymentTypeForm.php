<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\PaymentType\CreatePaymentType;
use FluxErp\Actions\PaymentType\DeletePaymentType;
use FluxErp\Actions\PaymentType\UpdatePaymentType;
use FluxErp\Models\PaymentType;
use Livewire\Attributes\Locked;

class PaymentTypeForm extends FluxForm
{
    public ?int $tenant_id = null;

    public ?array $tenants = [];

    public ?string $description = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public bool $is_default = false;

    public bool $is_direct_debit = false;

    public bool $is_purchase = false;

    public bool $is_sales = true;

    public ?string $name = null;

    public ?float $payment_discount_percentage = null;

    public ?int $payment_discount_target = null;

    public ?int $payment_reminder_days_1 = null;

    public ?int $payment_reminder_days_2 = null;

    public ?int $payment_reminder_days_3 = null;

    public ?string $payment_reminder_email_text = null;

    public ?string $payment_reminder_text = null;

    public ?int $payment_target = null;

    public bool $requires_manual_transfer = false;

    public function fill($values): void
    {
        if ($values instanceof PaymentType) {
            $values->loadMissing(['tenants:id']);

            $values = $values->toArray();
            $values['tenants'] = array_column($values['tenants'] ?? [], 'id');
        }

        parent::fill($values);
        $this->payment_discount_percentage = bcmul($this->payment_discount_percentage, 100);
    }

    protected function getActions(): array
    {
        return [
            'create' => CreatePaymentType::class,
            'update' => UpdatePaymentType::class,
            'delete' => DeletePaymentType::class,
        ];
    }

    protected function makeAction(string $name, ?array $data = null): FluxAction
    {
        if (is_null($data)) {
            $data = $this->toArray();
            $data['payment_discount_percentage'] = bcdiv($data['payment_discount_percentage'], 100);
        }

        return parent::makeAction($name, $data);
    }
}
