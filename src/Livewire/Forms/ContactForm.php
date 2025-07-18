<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Contact\CreateContact;
use FluxErp\Actions\Contact\DeleteContact;
use FluxErp\Actions\Contact\RestoreContact;
use FluxErp\Actions\Contact\UpdateContact;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use Livewire\Attributes\Locked;

class ContactForm extends FluxForm
{
    public ?int $agent_id = null;

    public ?int $approval_user_id = null;

    public array $categories = [];

    public ?int $client_id = null;

    public ?float $credit_line = null;

    public ?string $creditor_number = null;

    public ?int $currency_id = null;

    public ?string $customer_number = null;

    public ?string $customs_identifier = null;

    public ?string $debtor_number = null;

    public ?int $discount_days = null;

    public ?float $discount_percent = null;

    public ?string $footer = null;

    public bool $has_delivery_lock = false;

    public ?string $header = null;

    #[Locked]
    public ?int $id = null;

    public array $industries = [];

    public array $main_address = [
        'client_id' => null,
        'country_id' => null,
        'language_id' => null,
        'salutation' => null,
    ];

    #[Locked]
    public ?int $main_address_id = null;

    public ?int $payment_reminder_days_1 = null;

    public ?int $payment_reminder_days_2 = null;

    public ?int $payment_reminder_days_3 = null;

    public ?int $payment_target_days = null;

    public ?int $payment_type_id = null;

    public ?int $price_list_id = null;

    public ?int $purchase_payment_type_id = null;

    public int $rating = 0;

    public ?int $record_origin_id = null;

    public ?string $vat_id = null;

    public ?int $vat_rate_id = null;

    public function fill($values): void
    {
        if ($values instanceof Contact) {
            $values->loadMissing(['categories:id', 'industries:id']);

            $values = $values->toArray();
            $values['categories'] = array_column($values['categories'] ?? [], 'id');
            $values['industries'] = array_column($values['industries'] ?? [], 'id');
        }

        parent::fill($values);
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);

        $this->main_address['client_id'] = resolve_static(Client::class, 'query')->where('is_active', true)->count() === 1
            ? resolve_static(Client::class, 'query')->where('is_active', true)->first()->id
            : null;
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateContact::class,
            'restore' => RestoreContact::class,
            'update' => UpdateContact::class,
            'delete' => DeleteContact::class,
        ];
    }
}
