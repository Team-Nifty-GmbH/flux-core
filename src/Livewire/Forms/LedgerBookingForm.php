<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\LedgerBooking\CreateLedgerBooking;
use FluxErp\Actions\LedgerBooking\DeleteLedgerBooking;
use FluxErp\Actions\LedgerBooking\UpdateLedgerBooking;
use Livewire\Attributes\Locked;

class LedgerBookingForm extends FluxForm
{
    public ?float $amount = null;

    public ?string $booking_date = null;

    public ?string $booking_text = null;

    public ?int $credit_ledger_account_id = null;

    public ?int $debit_ledger_account_id = null;

    #[Locked]
    public ?int $id = null;

    public ?string $note = null;

    public ?int $tenant_id = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateLedgerBooking::class,
            'update' => UpdateLedgerBooking::class,
            'delete' => DeleteLedgerBooking::class,
        ];
    }
}
