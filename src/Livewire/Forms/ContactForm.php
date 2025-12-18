<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Contact\CreateContact;
use FluxErp\Actions\Contact\DeleteContact;
use FluxErp\Actions\Contact\RestoreContact;
use FluxErp\Actions\Contact\UpdateContact;
use FluxErp\Models\Contact;
use FluxErp\Models\Country;
use FluxErp\Models\Language;
use FluxErp\Models\RecordOrigin;
use FluxErp\Models\Tenant;
use FluxErp\Support\Livewire\Attributes\RenderAs;
use FluxErp\Support\Livewire\Attributes\SeparatorAfter;
use FluxErp\Traits\Livewire\Form\SupportsAutoRender;
use Livewire\Attributes\Locked;

class ContactForm extends FluxForm
{
    use SupportsAutoRender;

    public ?int $agent_id = null;

    public ?int $approval_user_id = null;

    public array $categories = [];

    #[RenderAs(
        type: RenderAs::SELECT,
        options: [
            'select' => 'label:name|value:id',
            ':options' => "resolve_static(\FluxErp\Models\Tenant::class, 'query')->where('is_active', true)->get(['id', 'name'])",
        ],
        label: 'Tenant',
    )]
    public ?int $tenant_id = null;

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

    public array $main_address = [];

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

    #[RenderAs(
        type: RenderAs::SELECT,
        options: [
            'select' => 'label:name|value:id',
            'searchable' => true,
            'unfiltered' => true,
            ':request' => "[
                'url' => route('search', \FluxErp\Models\RecordOrigin::class),
                'method' => 'POST',
                'params' => [
                    'searchFields' => ['name'],
                    'where' => [
                        ['model_type', '=', morph_alias(\FluxErp\Models\Contact::class)],
                        ['is_active', '=', true],
                    ],
                ],
            ]",
        ],
        label: 'Contact Origin',
    )]
    public ?int $record_origin_id = null;

    public ?string $vat_id = null;

    public ?int $vat_rate_id = null;

    public ?string $company = null;

    #[RenderAs(
        type: RenderAs::SELECT,
        options: [
            ':options' => "resolve_static(\FluxErp\Enums\SalutationEnum::class, 'valuesLocalized')",
        ],
    )]
    public ?string $salutation = null;

    public ?string $title = null;

    public ?string $firstname = null;

    public ?string $lastname = null;

    public ?string $street = null;

    #[RenderAs(
        type: RenderAs::INPUT,
        label: 'Zip',
        group: 'zip-city',
    )]
    public ?string $zip = null;

    #[RenderAs(
        type: RenderAs::INPUT,
        label: 'City',
        group: 'zip-city',
    )]
    public ?string $city = null;

    #[RenderAs(
        type: RenderAs::SELECT,
        options: [
            'select' => 'label:name|value:id',
            'searchable' => true,
            ':options' => "resolve_static(\FluxErp\Models\Country::class, 'query')->get(['id', 'name'])",
        ],
        label: 'Country',
    )]
    public ?int $country_id = null;

    #[RenderAs(
        type: RenderAs::SELECT,
        options: [
            'select' => 'label:name|value:id',
            'searchable' => true,
            ':options' => "resolve_static(\FluxErp\Models\Language::class, 'query')->get(['id', 'name'])",
        ],
        label: 'Language',
    )]
    #[SeparatorAfter]
    public ?int $language_id = null;

    public ?string $email_primary = null;

    public ?string $phone = null;

    public ?string $phone_mobile = null;

    public function fill($values): void
    {
        if ($values instanceof Contact) {
            $values->loadMissing(['categories:id', 'industries:id']);

            $values = $values->toArray();
            $values['categories'] = array_column($values['categories'] ?? [], 'id');
            $values['industries'] = array_column($values['industries'] ?? [], 'id');
        }

        parent::fill($values);

        // Convert discount percent from decimal to percent for display
        $this->discount_percent = ! is_null($this->discount_percent)
            ? bcmul($this->discount_percent, 100)
            : null;
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);

        $this->tenant_id = resolve_static(Tenant::class, 'default')->getKey();
        $this->language_id = resolve_static(Language::class, 'default')->getKey();
        $this->country_id = resolve_static(Country::class, 'default')->getKey();
    }

    public function toActionData(): array
    {
        $data = parent::toActionData();

        // Convert discount percent from percent to decimal for storage
        $data['discount_percent'] = ! is_null($this->discount_percent)
            ? bcdiv($this->discount_percent, 100)
            : null;

        $flatAddressFields = [
            'company',
            'salutation',
            'title',
            'firstname',
            'lastname',
            'street',
            'zip',
            'city',
            'country_id',
            'language_id',
            'email_primary',
            'phone',
            'phone_mobile',
        ];

        foreach ($flatAddressFields as $field) {
            if ($this->{$field} !== null) {
                $data['main_address'][$field] = $this->{$field};
            }
        }

        foreach ($flatAddressFields as $field) {
            unset($data[$field]);
        }

        return $data;
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

    protected function renderAsModal(): bool
    {
        return true;
    }

    protected function shouldRenderProperty(string $propertyName): bool
    {
        return match ($propertyName) {
            'tenant_id' => resolve_static(Tenant::class, 'query')->where('is_active', true)->count() > 1,
            'country_id' => resolve_static(Country::class, 'query')->count() > 1,
            'language_id' => resolve_static(Language::class, 'query')->count() > 1,
            'record_origin_id' => resolve_static(RecordOrigin::class, 'query')
                ->where('model_type', morph_alias(Contact::class))
                ->where('is_active', true)
                ->exists(),
            default => true,
        };
    }
}
