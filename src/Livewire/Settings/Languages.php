<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\LanguageList;
use FluxErp\Livewire\Forms\LanguageForm;
use FluxErp\Models\Language;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Languages extends LanguageList
{
    use Actions;

    protected ?string $includeBefore = 'flux::livewire.settings.languages';

    public LanguageForm $selectedLanguage;

    public bool $editModal = false;

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create'))
                ->color('primary')
                ->icon('plus')
                ->attributes([
                    'x-on:click' => '$wire.showEditModal()',
                ]),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->color('primary')
                ->icon('pencil')
                ->attributes([
                    'x-on:click' => '$wire.showEditModal(record.id)',
                ]),
        ];
    }

    public function showEditModal(?int $languageId = null): void
    {
        if (! $languageId) {
            $this->selectedLanguage->reset();
        } else {
            $this->selectedLanguage->fill(
                resolve_static(Language::class, 'query')
                    ->whereKey($languageId)
                    ->first()
            );
        }

        $this->editModal = true;
        $this->resetErrorBag();
    }

    public function save(): bool
    {
        try {
            $this->selectedLanguage->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function delete(): bool
    {
        try {
            $this->selectedLanguage->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}
