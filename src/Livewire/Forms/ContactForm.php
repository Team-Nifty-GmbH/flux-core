<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Contact\CreateContact;
use FluxErp\Actions\Contact\DeleteContact;
use FluxErp\Actions\Contact\UpdateContact;
use FluxErp\Models\Client;
use FluxErp\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ContactForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?int $approval_user_id = null;

    public ?int $payment_type_id = null;

    public ?int $price_list_id = null;

    public ?int $client_id = null;

    public ?int $agent_id = null;

    public ?int $countryId = null;

    public ?int $language_id = null;

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

    public array $main_address = [];

    public array $categories = [];

    public function __construct(
        protected Component $component,
        protected $propertyName
    ) {
        parent::__construct($component, $propertyName);

        $this->main_address['client_id'] = null;
        $this->main_address['country_id'] = null;
        $this->main_address['language_id'] = null;
    }

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

        $this->main_address['client_id'] = Client::query()->where('is_active', true)->count() === 1
            ? Client::query()->where('is_active', true)->first()->id
            : null;
        $this->main_address['language_id'] = Language::query()->count() === 1 ? Language::query()->first()->id : null;
    }

    public function fill($values): void
    {
        parent::fill($values);

        if ($values instanceof Model) {
            $this->categories = $values->categories?->pluck('id')->toArray() ?? [];
        }
    }
}
