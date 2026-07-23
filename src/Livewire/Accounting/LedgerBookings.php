<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Actions\LedgerBooking\CreateLedgerBooking;
use FluxErp\Actions\LedgerBooking\DeleteLedgerBooking;
use FluxErp\Actions\LedgerBooking\UpdateLedgerBooking;
use FluxErp\Livewire\DataTables\LedgerBookingList;
use FluxErp\Livewire\Forms\LedgerBookingForm;
use FluxErp\Models\LedgerBooking;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class LedgerBookings extends LedgerBookingList
{
    public ?string $includeBefore = 'flux::livewire.accounting.ledger-bookings';

    public LedgerBookingForm $ledgerBooking;

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('New'))
                ->icon('plus')
                ->color('indigo')
                ->when(resolve_static(CreateLedgerBooking::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit()',
                ]),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->icon('pencil')
                ->color('indigo')
                ->when(resolve_static(UpdateLedgerBooking::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit(record.id)',
                ]),
            DataTableButton::make()
                ->text(__('Delete'))
                ->icon('trash')
                ->color('red')
                ->when(resolve_static(DeleteLedgerBooking::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Ledger Booking')]),
                    'wire:click' => 'delete(record.id)',
                ]),
        ];
    }

    public function delete(LedgerBooking $ledgerBooking): bool
    {
        $this->ledgerBooking->reset();
        $this->ledgerBooking->fill($ledgerBooking);

        try {
            $this->ledgerBooking->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function edit(?LedgerBooking $ledgerBooking = null): void
    {
        $this->ledgerBooking->reset();

        if ($ledgerBooking?->exists) {
            $this->ledgerBooking->fill($ledgerBooking);
        }

        $this->modalOpen('edit-ledger-booking-modal');
    }

    public function save(): bool
    {
        try {
            $this->ledgerBooking->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}
