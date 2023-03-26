<?php

namespace FluxErp\Http\Livewire\Settings;

use FluxErp\Http\Requests\CreateTranslationRequest;
use FluxErp\Http\Requests\UpdateTranslationRequest;
use FluxErp\Models\LanguageLine;
use FluxErp\Rules\UniqueInFieldDependence;
use FluxErp\Services\TranslationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Livewire\Component;
use WireUi\Traits\Actions;

class TranslationEdit extends Component
{
    use Actions;

    public array $translation;

    public string $locale;

    public bool $isNew;

    protected $listeners = [
        'show',
        'save',
        'delete',
    ];

    protected function getRules(): array
    {
        $rules = $this->isNew ?
            (new CreateTranslationRequest())->rules() : (new UpdateTranslationRequest())->rules();

        foreach ($rules['key'] as $key => $rule) {
            if ($rule instanceof UniqueInFieldDependence) {
                $rules['key'][$key] = $this->isNew ?
                    new UniqueInFieldDependence(
                        LanguageLine::class,
                        'translation.group',
                        ! $this->isNew
                    ) :
                    new UniqueInFieldDependence(
                        LanguageLine::class,
                        'translation.group',
                        ! $this->isNew,
                        'translation.id'
                    );
            }
        }

        return Arr::prependKeysWith($rules, 'translation.');
    }

    public function boot(): void
    {
        $this->translation = array_fill_keys(
            array_keys((new CreateTranslationRequest())->rules()),
            null
        );

        $this->translation['text'] = [];
        $this->translation['translation'] = null;

        $this->locale = app()->getLocale();
        $this->isNew = true;
    }

    public function render(): View
    {
        return view('flux::livewire.settings.translation-edit');
    }

    public function show(string $locale, array $translation = []): void
    {
        $this->translation = $translation ?:
            array_fill_keys(
                array_keys((new CreateTranslationRequest())->rules()),
                null
            );

        $this->locale = $locale;

        if (is_null($this->translation['text'] ?? null)) {
            $this->translation['text'] = [];
        } else {
            $this->translation['translation'] = $translation['text'][$locale] ?? null;
        }

        $this->isNew = ! array_key_exists('id', $this->translation);
    }

    public function save(): void
    {
        if (($this->isNew && ! user_can('api.translations.{id}.post')) ||
            (! $this->isNew && ! user_can('api.translations.{id}.put'))
        ) {
            $this->notification()->error(
                __('insufficient permissions'),
                __('You have not the rights to modify this record')
            );

            return;
        }

        if ($this->translation['translation'] ?? false) {
            if ($this->isNew) {
                $this->translation['text'] = [$this->locale => $this->translation['translation']];
            } else {
                $this->translation['text'][$this->locale] = $this->translation['translation'];
            }
        }

        $validated = $this->validate();

        $translationService = new TranslationService();
        $response = $translationService->{$this->isNew ? 'create' : 'update'}($validated['translation']);

        if (! $this->isNew && $response['status'] > 299) {
            $this->notification()->error(
                implode(',', array_keys($response['errors'])),
                implode(', ', Arr::dot($response['errors']))
            );

            return;
        }

        $this->notification()->success(__('Translation saved successful.'));

        $this->skipRender();
        $this->emitUp('closeModal', $this->isNew ? $response : $response['data']);
    }

    public function delete(): void
    {
        if (! user_can('api.translations.{id}.delete')) {
            return;
        }

        (new TranslationService())->delete($this->translation['id']);

        $this->skipRender();
        $this->emitUp('closeModal', $this->translation, true);
    }
}
