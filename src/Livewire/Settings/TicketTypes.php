<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\TicketType;
use FluxErp\Rulesets\AdditionalColumn\CreateAdditionalColumnRuleset;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Livewire\Attributes\Locked;
use Livewire\Component;

class TicketTypes extends Component
{
    #[Locked]
    public array $dbTicketTypes = [];

    #[Locked]
    public array $additionalColumns = [];

    public array $ticketTypes;

    public int $ticketTypeIndex = -1;

    public int $additionalColumnIndex = -1;

    public bool $showTicketTypeModal = false;

    public bool $showAdditionalColumnModal = false;

    protected $listeners = [
        'closeModal',
    ];

    public function mount(): void
    {
        $this->dbTicketTypes = resolve_static(TicketType::class, 'query')
            ->orderBy('name')
            ->get(['id', 'name', 'model_type'])
            ->toArray();

        $this->ticketTypes = [];
        foreach ($this->dbTicketTypes as $key => $ticketType) {
            $this->ticketTypes[] = [
                'id' => $ticketType['id'],
                'name' => $ticketType['name'],
                'model_type' => $ticketType['model_type'],
            ];

            $additionalColumns = resolve_static(AdditionalColumn::class, 'query')
                ->where('model_type', morph_alias(TicketType::class))
                ->where('model_id', $ticketType['id'])
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'model_type',
                    'model_id',
                    'field_type',
                    'label',
                    'validations',
                    'values',
                    'is_customer_editable',
                ])
                ->toArray();

            $this->ticketTypes = array_merge(
                $this->ticketTypes,
                $additionalColumns
            );

            $this->dbTicketTypes[$key]['additional_columns'] = $additionalColumns;
            $this->additionalColumns = array_merge($this->additionalColumns, $additionalColumns);
        }

        $this->sortTicketTypes($this->dbTicketTypes);
    }

    public function render(): View
    {
        return view('flux::livewire.settings.ticket-types');
    }

    public function show(?int $index = null, bool $newAdditionalColumn = false): void
    {
        if (is_null($index)) {
            $this->ticketTypeIndex = -1;
            $this->additionalColumnIndex = -1;
        } elseif (array_key_exists('field_type', $this->ticketTypes[$index])) {
            $this->ticketTypeIndex = -1;
            $this->additionalColumnIndex = $index;
        } else {
            $this->ticketTypeIndex = $index;
            $this->additionalColumnIndex = -1;
        }

        if ($this->additionalColumnIndex !== -1 || $newAdditionalColumn) {
            $this->dispatch(
                'show',
                ! $newAdditionalColumn ? $this->ticketTypes[$index] :
                    array_merge(
                        array_fill_keys(
                            array_keys(resolve_static(CreateAdditionalColumnRuleset::class, 'getRules')),
                            null
                        ),
                        [
                            'model_type' => morph_alias(TicketType::class),
                            'model_id' => $this->ticketTypes[$index]['id'],
                        ]
                    )
            )->to('settings.additional-column-edit');

            $this->showAdditionalColumnModal = true;
        } else {
            $this->dispatch(
                'show',
                ! is_null($index) ? $this->ticketTypes[$index] : []
            )->to('settings.ticket-type-edit');

            $this->showTicketTypeModal = true;
        }
    }

    public function closeModal(array $data, bool $delete = false): void
    {
        if ($data['field_type'] ?? false) {
            $key = array_search($data['model_id'], array_column($this->dbTicketTypes, 'id'));
            $additionalColumnKey = array_search($data['id'], array_column($this->additionalColumns, 'id'));

            if (! $delete && $additionalColumnKey === false && $key !== false) {
                $this->dbTicketTypes[$key]['additional_columns'][] = $data;
            } elseif ($additionalColumnKey !== false && $key !== false) {
                $additionalColumnId = $this->additionalColumns[$additionalColumnKey]['id'];

                $index = array_search(
                    $additionalColumnId,
                    array_column($this->dbTicketTypes[$key]['additional_columns'], 'id')
                );
                if ($index !== false) {
                    if (! $delete) {
                        $this->dbTicketTypes[$key]['additional_columns'][$index] = $data;
                    } else {
                        unset($this->dbTicketTypes[$key]['additional_columns'][$index]);
                    }
                }
            }
        } else {
            $key = array_search($data['id'], array_column($this->dbTicketTypes, 'id'));

            if ($delete) {
                unset($this->dbTicketTypes[$key]);
            } elseif ($key === false) {
                $this->dbTicketTypes[] = $data;
            } else {
                $this->dbTicketTypes[$key] = array_merge(
                    $data,
                    [
                        'additional_columns' => [
                            $this->dbTicketTypes[$key]['additional_columns'] ?? [],
                        ],
                    ]
                );
            }
        }

        $this->sortTicketTypes($this->dbTicketTypes);

        $this->ticketTypeIndex = -1;
        $this->additionalColumnIndex = -1;
        $this->showTicketTypeModal = false;
        $this->showAdditionalColumnModal = false;
    }

    public function delete(bool $additionalColumn = false): void
    {
        if ($additionalColumn) {
            $this->dispatch('delete')->to('settings.additional-column-edit');
        } else {
            $this->dispatch('delete')->to('settings.ticket-type-edit');
        }
    }

    private function sortTicketTypes(array $ticketTypes): void
    {
        $sorted = Arr::sort(
            Arr::sort(
                $ticketTypes,
                fn ($value) => strtolower($value['name'])
            ),
            fn ($value) => strtolower($value['additional_columns']['name'] ?? '')
        );

        $ticketTypes = [];
        foreach ($sorted as $ticketType) {
            $additionalColumns = $ticketType['additional_columns'] ?? [];
            unset($ticketType['additional_columns']);

            $ticketTypes = array_merge($ticketTypes, [$ticketType], $additionalColumns);
        }

        $this->ticketTypes = array_filter($ticketTypes);
    }
}
