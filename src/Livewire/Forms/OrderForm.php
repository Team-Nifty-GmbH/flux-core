<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Actions\Order\DeleteOrder;
use FluxErp\Actions\Order\UpdateLockedOrder;
use FluxErp\Actions\Order\UpdateOrder;
use FluxErp\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;

class OrderForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    #[Locked]
    public ?string $uuid = null;

    public ?int $approval_user_id = null;

    public ?int $parent_id = null;

    public ?int $client_id = null;

    public ?int $agent_id = null;

    public ?int $contact_id = null;

    public ?int $contact_bank_connection_id = null;

    public ?int $address_invoice_id = null;

    public ?int $address_delivery_id = null;

    public ?int $language_id = null;

    public ?int $order_type_id = null;

    public ?int $price_list_id = null;

    public ?int $payment_type_id = null;

    public ?int $responsible_user_id = null;

    public ?array $address_invoice = null;

    public ?array $address_delivery = null;

    public ?string $state = null;

    public ?string $payment_state = null;

    public ?string $delivery_state = null;

    public ?int $payment_target = null;

    public ?int $payment_discount_target = null;

    public ?float $payment_discount_percent = null;

    public ?string $total_base_net_price = null;

    public ?string $total_base_gross_price = null;

    #[Locked]
    public ?float $gross_profit = 0;

    #[Locked]
    public ?float $margin = 0;

    public ?string $total_net_price = null;

    public ?string $total_gross_price = null;

    public ?array $total_vats = null;

    public ?float $balance = null;

    public ?int $payment_reminder_days_1 = null;

    public ?int $payment_reminder_days_2 = null;

    public ?int $payment_reminder_days_3 = null;

    public ?int $payment_reminder_current_level = null;

    public ?string $payment_reminder_next_date = null;

    public ?string $order_number = null;

    public ?string $commission = null;

    public ?string $iban = null;

    public ?string $account_holder = null;

    public ?string $bank_name = null;

    public ?string $bic = null;

    public ?string $header = null;

    public ?string $footer = null;

    public ?string $logistic_note = null;

    public ?array $payment_texts = [];

    public ?string $order_date = null;

    public ?string $invoice_date = null;

    public ?string $invoice_number = null;

    public ?string $system_delivery_date = null;

    public ?string $system_delivery_date_end = null;

    public bool $is_locked = false;

    public bool $is_confirmed = false;

    public ?array $currency = null;

    public ?array $order_type = null;

    public ?string $created_at = null;

    public ?string $created_by = null;

    public ?string $updated_at = null;

    public ?string $updated_by = null;

    public array $users = [];

    #[Locked]
    public ?array $invoice = null;

    #[Locked]
    public ?array $parent = null;

    #[Locked]
    public bool $isPurchase = false;

    #[Locked]
    public bool $hasContactDeliveryLock = false;

    protected function getActions(): array
    {
        return [
            'create' => CreateOrder::class,
            'update' => UpdateOrder::class,
            'update_locked' => UpdateLockedOrder::class,
            'delete' => DeleteOrder::class,
        ];
    }

    public function fill($values): void
    {
        if ($values instanceof Model) {
            $values->loadMissing('parent');
            $this->isPurchase = $values->orderType->order_type_enum->isPurchase();
        }

        parent::fill($values);

        if ($values instanceof Order) {
            $this->hasContactDeliveryLock = $values->contact->has_delivery_lock;
            $this->parent = $this->parent
                ? [
                    'label' => $values->parent->getLabel(),
                    'url' => $values->parent->getUrl(),
                ]
                : null;
        }
    }

    public function save(): void
    {
        if ($this->{$this->getKey()} && ! $this->is_locked) {
            $this->update();
        } elseif ($this->{$this->getKey()} && $this->is_locked) {
            $this->updateLocked();
        } else {
            $this->create();
        }
    }

    public function updateLocked(): void
    {
        $response = $this->makeAction('update_locked')
            ->when($this->checkPermission, fn (FluxAction $action) => $action->checkPermission())
            ->validate()
            ->execute();

        $this->actionResult = $response;

        $this->fill($response);
    }

    protected function makeAction(string $name, ?array $data = null): FluxAction
    {
        $data = $this->toArray();

        if (! $this->id) {
            unset(
                $data['state'],
                $data['payment_state'],
                $data['delivery_state'],
                $data['order_number'],
                $data['order_date'],
                $data['invoice_date'],
                $data['invoice_number'],
                $data['payment_reminder_current_level'],
                $data['payment_reminder_next_date'],
            );
        }

        return parent::makeAction($name, $data);
    }
}
