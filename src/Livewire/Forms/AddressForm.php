<?php

namespace FluxErp\Livewire\Forms;

use Carbon\Carbon;
use FluxErp\Actions\Address\CreateAddress;
use FluxErp\Actions\Address\DeleteAddress;
use FluxErp\Actions\Address\UpdateAddress;
use FluxErp\Models\Address;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;

class AddressForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?int $client_id = null;

    public ?int $language_id = null;

    public ?int $country_id = null;

    public ?int $contact_id = null;

    public ?string $company = null;

    public ?string $title = null;

    public ?string $salutation = null;

    public ?string $firstname = null;

    public ?string $lastname = null;

    public ?string $name = null;

    public ?string $addition = null;

    public ?string $mailbox = null;

    public ?string $mailbox_city = null;

    public ?string $mailbox_zip = null;

    public string|float|null $latitude = null;

    public string|float|null $longitude = null;

    public ?string $zip = null;

    public ?string $city = null;

    public ?string $street = null;

    public ?string $url = null;

    public ?string $email_primary = null;

    public ?string $phone = null;

    public ?string $date_of_birth = null;

    public ?string $department = null;

    public ?string $email = null;

    public ?string $password = null;

    public ?bool $has_formal_salutation = null;

    public bool $is_main_address = false;

    public bool $is_invoice_address = false;

    public bool $is_delivery_address = false;

    public bool $is_active = true;

    public bool $can_login = false;

    // relations
    public array $additional_columns = [];

    public array $contact_options = [];

    public ?array $permissions = null;

    public array $tags = [];

    protected function getActions(): array
    {
        return [
            'create' => CreateAddress::class,
            'update' => UpdateAddress::class,
            'delete' => DeleteAddress::class,
        ];
    }

    public function fill($values): void
    {
        if ($values instanceof Address) {
            $values->loadMissing(['contactOptions', 'tags:id', 'permissions:id']);

            $values = $values->toArray();
            $values['tags'] = array_column($values['tags'] ?? [], 'id');
            $values['permissions'] = array_column($values['permissions'] ?? [], 'id');
        } elseif (data_get($values, 'id')) {
            $address = app(Address::class)->query()
                ->whereKey(data_get($values, 'id'))
                ->with(['contactOptions', 'tags:id', 'permissions:id'])
                ->first(['id']);

            if ($address) {
                $values['contact_options'] ??= $address->contactOptions->toArray();
                $values['tags'] ??= $address->tags->pluck('id')->toArray();
                $values['permissions'] ??= $address->permissions->pluck('id')->toArray();
            }
        }

        parent::fill($values);

        if (! is_null($this->date_of_birth)) {
            $this->date_of_birth = Carbon::create($this->date_of_birth)
                ->locale(app()->getLocale())
                ->isoFormat('L');
        }
    }

    public function toArray(): array
    {
        $data = parent::toArray();

        if (is_null($this->password)) {
            unset($data['password']);
        }

        $data['contact_options'] = array_filter($this->contact_options);

        return $data;
    }

    #[Computed]
    public function postalAddress(): array
    {
        return app(Address::class)->fill($this->toArray())->postal_address;
    }
}
