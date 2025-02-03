<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Ticket\CreateTicket;
use FluxErp\Actions\Ticket\DeleteTicket;
use FluxErp\Actions\Ticket\UpdateTicket;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Ticket;
use FluxErp\Models\Ticket as TicketModel;
use FluxErp\Models\TicketType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Livewire\Attributes\Locked;

class TicketForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?string $authenticatable_type = null;

    public ?int $authenticatable_id = null;

    public ?string $model_type = null;

    public ?int $model_id = null;

    public ?int $ticket_type_id = null;

    public ?string $ticket_number = null;

    public ?string $title = null;

    public ?string $description = null;

    public ?string $state = null;

    public ?string $created_at = null;

    public ?string $created_by = null;

    public ?string $updated_at = null;

    public ?string $updated_by = null;

    public array $users = [];

    public array $authenticatable = [];

    public ?array $ticket_type = null;

    public ?array $additional_columns = [];

    public ?array $availableAdditionalColumns = [];

    protected function getActions(): array
    {
        return [
            'create' => CreateTicket::class,
            'update' => UpdateTicket::class,
            'delete' => DeleteTicket::class,
        ];
    }

    public function fill($values): void
    {
        if ($values instanceof Ticket) {
            $values->loadMissing([
                'authenticatable',
                'ticketType:id,name',
                'users:id',
            ]);
            data_set($values, 'authenticatable.avatar_url', $values->authenticatable?->getAvatarUrl());
            data_set($values, 'authenticatable.avatar_url', $values->authenticatable?->getAvatarUrl());
            data_set($values, 'authenticatable.name', $values->authenticatable?->getLabel());
            $additionalColumns = $values->meta->keyBy('key')->toArray();

            $values = $values->toArray();
            data_set($values, 'users', array_column($values['users'], 'id'));
            $this->additional_columns = $additionalColumns;
        }

        parent::fill($values);

        $this->availableAdditionalColumns = resolve_static(AdditionalColumn::class, 'query')
            ->where('is_frontend_visible', true)
            ->where(function (Builder $query) {
                $query->where('model_type', morph_alias(TicketModel::class))
                    ->when($this->ticket_type_id, function (Builder $query) {
                        $query->orWhere(function (Builder $query) {
                            $query->where('model_type', morph_alias(TicketType::class))
                                ->where('model_id', $this->ticket_type_id);
                        });
                    });
            })
            ->get(['id', 'name', 'field_type', 'label', 'values'])
            ->toArray();

        $this->additional_columns = collect(
            array_merge(
                collect($this->availableAdditionalColumns)
                    ->map(function (array $column) {
                        return [
                            'key' => $column['name'],
                            'value' => $this->additional_columns[$column['name']]['value'] ?? null,
                            'label' => $column['label'],
                            'field_type' => $column['field_type'],
                            'values' => $column['values'],
                        ];
                    })
                    ->keyBy('key')
                    ->toArray(),
                $this->additional_columns
            )
        )
            ->map(function (array $column) {
                if (data_get($column, 'field_type') === 'checkbox') {
                    $column['value'] = (bool) data_get($column, 'value');
                }

                return $column;
            })
            ->toArray();
    }

    public function toActionData(): array
    {
        return array_merge(parent::toActionData(), Arr::pluck($this->additional_columns, 'value', 'key'));
    }
}
