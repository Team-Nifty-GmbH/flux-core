<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Contact\CreateContact;
use FluxErp\Actions\Contact\DeleteContact;
use FluxErp\Actions\Contact\UpdateContact;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use Livewire\Attributes\Locked;

class ContactForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?int $approval_user_id = null;

    public ?int $payment_type_id = null;

    public ?int $purchase_payment_type_id = null;

    public ?int $price_list_id = null;

    public ?int $client_id = null;

    public ?int $agent_id = null;

    public ?int $currency_id = null;

    public ?string $customer_number = null;

    public ?string $creditor_number = null;

    public ?string $debtor_number = null;

    public ?int $payment_target_days = null;

    public ?int $payment_reminder_days_1 = null;

    public ?int $payment_reminder_days_2 = null;

    public ?int $payment_reminder_days_3 = null;

    public ?int $discount_days = null;

    public ?float $discount_percent = null;

    public ?float $credit_line = null;

    public ?string $vat_id = null;

    public array $main_address = [
        'salutation' => null,
        'client_id' => null,
        'country_id' => null,
        'language_id' => null,
    ];

    public array $categories = [];

    protected function getActions(): array
    {
        return [
            'create' => CreateContact::class,
            'update' => UpdateContact::class,
            'delete' => DeleteContact::class,
        ];
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);

        $this->main_address['client_id'] = resolve_static(Client::class, 'query')->where('is_active', true)->count() === 1
            ? resolve_static(Client::class, 'query')->where('is_active', true)->first()->id
            : null;
    }

    public function fill($values): void
    {
        if ($values instanceof Contact) {
            $values->loadMissing(['categories:id']);

            $values = $values->toArray();
            $values['categories'] = array_column($values['categories'] ?? [], 'id');
        }

        parent::fill($values);
    }
}
