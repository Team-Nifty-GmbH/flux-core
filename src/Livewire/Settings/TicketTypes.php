<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\AdditionalColumn\CreateAdditionalColumn;
use FluxErp\Actions\TicketType\CreateTicketType;
use FluxErp\Actions\TicketType\DeleteTicketType;
use FluxErp\Actions\TicketType\UpdateTicketType;
use FluxErp\Livewire\DataTables\TicketTypesList;
use FluxErp\Livewire\Forms\TicketTypesForm;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Role;
use FluxErp\Models\TicketType;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class TicketTypes extends TicketTypesList {

    use Actions;

    protected ?string $includeBefore = 'flux::livewire.settings.ticket-types';

    public TicketTypesForm $ticketType;

    public function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'modelTypes' => model_info_all()
                    ->unique('morphClass')
                    ->map(fn ($modelInfo) => [
                        'label' => __(Str::headline($modelInfo->morphClass)),
                        'value' => $modelInfo->morphClass,
                    ])
                    ->sortBy('label')
                    ->toArray(),
                'roles' =>app(Role::class)->query()
                    ->where('guard_name', 'web')
                    ->get(['id', 'name'])
                    ->toArray()
            ]
        );
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create'))
                ->color('primary')
                ->icon('plus')
                ->when(resolve_static(CreateTicketType::class, 'canPerformAction', [false]))
                ->wireClick('edit'),
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
                ->label(__('Additional Column'))
                ->icon('plus')
                ->when(resolve_static(CreateAdditionalColumn::class, 'canPerformAction', [false]))
                ->wireClick('editAdditionalColumn(record.id, record.additional_column_id)'),

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

    public function save(): bool
    {

        try {
            $this->ticketType->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function delete (TicketType $ticketType): bool
    {
        $this->ticketType->reset();
        $this->ticketType->fill($ticketType);

        try {
            $this->ticketType->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function editAdditionalColumn(TicketType $ticketType, AdditionalColumn $additionalColumn): void
    {
        $this->additionalColumn->reset();
        $this->additionalColumn->fill($additionalColumn);

        $this->additionalColumn->model_type = $ticketType->getMorphClass();
        $this->additionalColumn->model_id = $ticketType->id;
    }
}
