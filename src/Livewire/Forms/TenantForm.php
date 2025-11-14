<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Tenant\CreateTenant;
use FluxErp\Actions\Tenant\DeleteTenant;
use FluxErp\Actions\Tenant\UpdateTenant;
use Livewire\Attributes\Locked;

class TenantForm extends FluxForm
{
    public ?array $bank_connections = null;

    public ?string $ceo = null;

    public ?string $city = null;

    public ?string $tenant_code = null;

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

    public ?string $tax_id = null;

    public ?string $terms_and_conditions = null;

    public ?string $vat_id = null;

    public ?string $website = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateTenant::class,
            'update' => UpdateTenant::class,
            'delete' => DeleteTenant::class,
        ];
    }
}
