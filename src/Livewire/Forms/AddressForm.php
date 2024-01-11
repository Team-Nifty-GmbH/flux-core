<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Address\CreateAddress;
use FluxErp\Actions\Address\DeleteAddress;
use FluxErp\Actions\Address\UpdateAddress;
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

    public ?string $email = null;

    public ?string $phone = null;

    public ?string $date_of_birth = null;

    public ?string $department = null;

    public ?string $login_name = null;

    public ?string $login_password = null;

    public bool $is_main_address = false;

    public bool $is_invoice_address = false;

    public bool $is_delivery_address = false;

    public bool $is_active = true;

    public bool $can_login = false;

    // relations
    public array $contact_options = [];

    public array $permissions = [];

    public array $additional_columns = [];

    protected function getActions(): array
    {
        return [
            'create' => CreateAddress::class,
            'update' => UpdateAddress::class,
            'delete' => DeleteAddress::class,
        ];
    }
}
