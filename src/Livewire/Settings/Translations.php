<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Models\Language;
use FluxErp\Models\LanguageLine;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Translations extends Component
{
    public array $translations;

    public array $locales;

    public string $locale;

    public int $index = -1;

    public bool $showTranslationModal = false;

    public string $search = '';

    protected $listeners = [
        'closeModal',
    ];

    public function mount(): void
    {
        $this->translations = app(LanguageLine::class)->all()->toArray();

        $this->locales = app(Language::class)->all('language_code')
            ->pluck('language_code')
            ->toArray();

        $this->locale = app()->getLocale();
    }

    public function render(): View
    {
        return view('flux::livewire.settings.translations');
    }

    public function show(?int $index = null): void
    {
        $this->index = is_null($index) ? -1 : $index;

        if (! is_null($index)) {
            $this->dispatch('show', $this->locale, $this->translations[$index])->to('settings.translation-edit');
        } else {
            $this->dispatch('show', $this->locale)->to('settings.translation-edit');
        }

        $this->showTranslationModal = true;
    }

    public function closeModal(array $translation, bool $delete = false): void
    {
        $key = array_search($translation['id'], array_column($this->translations, 'id'));

        if (! $delete) {
            if ($key === false) {
                $this->translations[] = $translation;
            } else {
                $this->translations[$key] = $translation;
            }
        } elseif ($key !== false) {
            unset($this->translations[$key]);
        }

        $this->index = 0;
        $this->showTranslationModal = false;
        $this->skipRender();
    }

    public function delete(): void
    {
        $this->dispatch('delete')->to('settings.translation-edit');
    }

    public function updatedLocale(): void
    {
        $this->skipRender();
    }

    public function updatedSearch(): void
    {
        if ($this->search) {
            $result = app(LanguageLine::class)->search($this->search)->paginate();

            $this->translations = count($result->items()) ?
                $result->items() : app(LanguageLine::class)->all()->toArray();
        } else {
            $this->translations = app(LanguageLine::class)->all()->toArray();
        }

        $this->skipRender();
    }
}
