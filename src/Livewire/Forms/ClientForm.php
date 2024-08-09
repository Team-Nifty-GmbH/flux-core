<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Client\CreateClient;
use FluxErp\Actions\Client\DeleteClient;
use FluxErp\Actions\Client\UpdateClient;
use Livewire\Attributes\Locked;

class ClientForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?int $country_id = null;

    public ?string $name = null;

    public ?string $client_code = null;

    public ?string $ceo = null;

    public ?string $street = null;

    public ?string $city = null;

    public ?string $postcode = null;

    public ?string $phone = null;

    public ?string $fax = null;

    public ?string $email = null;

    public ?string $website = null;

    public ?string $creditor_identifier = null;

    public ?string $vat_id = null;

    public ?string $sepa_text = null;

    public ?array $opening_hours = [];

    public ?string $terms_and_conditions = null;

    public bool $is_active = true;

    public bool $is_default = false;

    public ?array $bank_connections = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateClient::class,
            'update' => UpdateClient::class,
            'delete' => DeleteClient::class,
        ];
    }
}
