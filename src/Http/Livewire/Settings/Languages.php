<?php

namespace FluxErp\Http\Livewire\Settings;

use FluxErp\Http\Requests\CreateLanguageRequest;
use FluxErp\Http\Requests\UpdateLanguageRequest;
use FluxErp\Models\Language;
use FluxErp\Services\LanguageService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Livewire\Component;
use WireUi\Traits\Actions;

class Languages extends Component
{
    use Actions;

    public array $selectedLanguage = [];

    public array $languages = [];

    public bool $editModal = false;

    public function getRules(): mixed
    {
        $languageRequest = ($this->selectedLanguage['id'] ?? false)
            ? new UpdateLanguageRequest()
            : new CreateLanguageRequest();

        return Arr::prependKeysWith($languageRequest->getRules($this->selectedLanguage),
            'selectedLanguage.');
    }

    public function boot(): void
    {
        $this->languages = Language::query()
            ->select(['id', 'name', 'iso_name', 'language_code'])
            ->get()
            ->toArray();
    }

    public function render(): View
    {
        return view('flux::livewire.settings.languages');
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

        $this->boot();
    }

    public function delete(): void
    {
        if (! user_can('api.languages.{id}.delete')) {
            return;
        }

        $collection = collect($this->languages);
        Language::query()
            ->whereKey($this->selectedLanguage['id'])
            ->first()
            ->delete();
        $this->languages = $collection
            ->whereNotIn('id', [$this->selectedLanguage['id']])
            ->toArray();
    }
}
