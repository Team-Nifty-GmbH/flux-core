<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Client\CreateClient;
use FluxErp\Actions\Client\DeleteClient;
use FluxErp\Actions\Client\UpdateClient;
use Livewire\Attributes\Locked;

class ClientForm extends FluxForm
{
    public ?array $bank_connections = null;

    public ?string $ceo = null;

    public ?string $city = null;

    public ?string $client_code = null;

    public ?int $commission_credit_note_order_type_id = null;

    public ?int $country_id = null;

    public ?string $creditor_identifier = null;

    public ?string $email = null;

    public ?string $fax = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public bool $is_default = false;

    public ?string $name = null;

    public ?array $opening_hours = [];

    public ?string $phone = null;

    public ?string $postcode = null;

    public ?string $sepa_text_b2b = null;

    public ?string $sepa_text_basic = null;

    public ?string $street = null;

    public ?string $terms_and_conditions = null;

    public ?string $vat_id = null;

    public ?string $website = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateClient::class,
            'update' => UpdateClient::class,
            'delete' => DeleteClient::class,
        ];
    }
}
