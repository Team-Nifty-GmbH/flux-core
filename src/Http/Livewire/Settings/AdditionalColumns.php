<?php

namespace FluxErp\Http\Livewire\Settings;

use FluxErp\Models\AdditionalColumn;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AdditionalColumns extends Component
{
    public array $additionalColumns;

    public int $index = -1;

    public bool $showAdditionalColumnModal = false;

    protected $listeners = [
        'closeModal',
    ];

    public function boot(): void
    {
        $this->additionalColumns = AdditionalColumn::query()
            ->whereNull('model_id')
            ->get()
            ->toArray();
    }

    public function render(): View
    {
        return view('flux::livewire.settings.additional-columns');
    }

    public function show(int $index = null): void
    {
        $this->index = is_null($index) ? -1 : $index;

        if (! is_null($index)) {
            $this->dispatch('show', $this->additionalColumns[$index])->to('settings.additional-column-edit');
        } else {
            $this->dispatch('show')->to('settings.additional-column-edit');
        }

        $this->showAdditionalColumnModal = true;
        $this->skipRender();
    }

    public function closeModal(array $additionalColumn, bool $delete = false): void
    {
        $key = array_search($additionalColumn['id'], array_column($this->additionalColumns, 'id'));

        if (! $delete) {
            if ($key === false) {
                $this->additionalColumns[] = $additionalColumn;
            } else {
                $this->additionalColumns[$key] = $additionalColumn;
            }
        } elseif ($key !== false) {
            unset($this->additionalColumns[$key]);
        }

        $this->index = 0;
        $this->showAdditionalColumnModal = false;
        $this->skipRender();
    }

    public function delete(): void
    {
        $this->dispatch('delete')->to('settings.additional-column-edit');
    }
}
