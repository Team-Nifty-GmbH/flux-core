<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Http\Requests\CreateLanguageRequest;
use FluxErp\Http\Requests\UpdateLanguageRequest;
use FluxErp\Livewire\DataTables\LanguageList;
use FluxErp\Models\Language;
use FluxErp\Services\LanguageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class Languages extends LanguageList
{
    use Actions;

    protected string $view = 'flux::livewire.settings.languages';

    public array $selectedLanguage = [];

    public bool $editModal = false;

    public function getRules(): mixed
    {
        $languageRequest = ($this->selectedLanguage['id'] ?? false)
            ? new UpdateLanguageRequest()
            : new CreateLanguageRequest();

        return Arr::prependKeysWith($languageRequest->getRules($this->selectedLanguage),
            'selectedLanguage.');
    }

    public function getTableActions(): array
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

    public function getRowActions(): array
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

    public function showEditModal(int $languageId = null): void
    {
        if (! $languageId) {
            $this->selectedLanguage = [];
        } else {
            $this->selectedLanguage = Language::query()
                ->whereKey($languageId)
                ->first()
                ->toArray();
        }

        $this->editModal = true;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        if (! user_can('api.languages.post')) {
            return;
        }

        $validated = $this->validate();

        $language = Language::query()
            ->whereKey($this->selectedLanguage['id'] ?? false)
            ->firstOrNew();

        $function = $language->exists ? 'update' : 'create';

        $response = (new LanguageService())->{$function}($validated['selectedLanguage']);

        if (($response['status'] ?? false) === 200 || $response instanceof Model) {
            $this->notification()->success('Successfully saved');
            $this->editModal = false;
        }

        $this->loadData();
    }

    public function delete(): void
    {
        if (! user_can('api.languages.{id}.delete')) {
            return;
        }

        Language::query()
            ->whereKey($this->selectedLanguage['id'])
            ->first()
            ->delete();
        $this->loadData();
    }
}
