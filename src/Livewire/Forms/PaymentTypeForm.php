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
    #[Locked]
    public ?int $id = null;

    public ?int $client_id = null;

    public ?string $name = null;

    public ?string $description = null;

    public ?int $payment_reminder_days_1 = null;

    public ?int $payment_reminder_days_2 = null;

    public ?int $payment_reminder_days_3 = null;

    public ?int $payment_target = null;

    public ?int $payment_discount_target = null;

    public ?float $payment_discount_percentage = null;

    public ?string $payment_reminder_text = null;

    public ?string $payment_reminder_email_text = null;

    public bool $is_active = true;

    public bool $is_direct_debit = false;

    public bool $is_default = false;

    public bool $is_purchase = false;

    public bool $is_sales = true;

    public bool $requires_manual_transfer = false;

    public ?array $clients = [];

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

    public function fill($values)
    {
        if ($values instanceof PaymentType) {
            $values->loadMissing(['clients:id']);

            $values = $values->toArray();
            $values['clients'] = array_column($values['clients'] ?? [], 'id');
        }

        parent::fill($values);
        $this->payment_discount_percentage = bcmul($this->payment_discount_percentage, 100);
    }
}
