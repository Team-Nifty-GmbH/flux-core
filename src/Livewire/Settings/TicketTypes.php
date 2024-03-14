<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\TicketType\CreateTicketType;
use FluxErp\Actions\TicketType\DeleteTicketType;
use FluxErp\Actions\TicketType\UpdateTicketType;
use FluxErp\Livewire\DataTables\TicketTypesList;
use FluxErp\Livewire\Forms\TicketTypesForm;
use FluxErp\Models\TicketType;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class TicketTypes extends TicketTypesList {

    use Actions;

    protected ?string $includeBefore = 'flux::livewire.settings.ticket-types';

    public TicketTypesForm $ticketType;

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create'))
                ->color('primary')
                ->icon('plus')
                ->when(resolve_static(CreateTicketType::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit()',
                ]),
        ];
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->icon('pencil')
                ->color('primary')
                ->when(resolve_static(UpdateTicketType::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit(record.id)',
                ]),
            DataTableButton::make()
                ->label(__('Delete'))
                ->color('negative')
                ->icon('trash')
                ->when(resolve_static(DeleteTicketType::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:confirm.icon.error' => __('wire:confirm.delete', ['model' => __('Ticket Type')]),
                ]),
        ];
    }

    public function edit(TicketType $ticketType): void
    {
        $this->ticketType->reset();
        $this->ticketType->fill($ticketType);

        $this->js(<<<'JS'
            $openModal('edit-ticket-type');
        JS);
    }
}
