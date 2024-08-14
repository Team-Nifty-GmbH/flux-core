<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Translation\CreateTranslation;
use FluxErp\Actions\Translation\DeleteTranslation;
use FluxErp\Actions\Translation\UpdateTranslation;
use FluxErp\Models\LanguageLine;
use FluxErp\Rules\UniqueInFieldDependence;
use FluxErp\Rulesets\Translation\CreateTranslationRuleset;
use FluxErp\Rulesets\Translation\UpdateTranslationRuleset;
use FluxErp\Services\TranslationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Livewire\Component;
use WireUi\Traits\Actions;

class TranslationEdit extends Component
{
    use Actions;

    public array $translation = [
        'group' => '*',
    ];

    public string $locale;

    public bool $isNew;

    protected $listeners = [
        'show',
        'save',
        'delete',
    ];

    public function getRules(): array
    {
        $rules = $this->isNew ?
            resolve_static(CreateTranslationRuleset::class, 'getRules') :
            resolve_static(UpdateTranslationRuleset::class, 'getRules');

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

    public function mount(): void
    {
        $this->translation = array_fill_keys(
            array_keys(resolve_static(CreateTranslationRuleset::class, 'getRules')),
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
        $this->resetErrorBag();

        $this->translation = $translation ?:
            array_fill_keys(
                array_keys(resolve_static(CreateTranslationRuleset::class, 'getRules')),
                null
            );

        $this->translation['group'] ??= '*';

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
        if (($this->isNew && ! resolve_static(CreateTranslation::class, 'canPerformAction', [false])) ||
            (! $this->isNew && ! resolve_static(UpdateTranslation::class, 'canPerformAction', [false]))
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

        $translationService = app(TranslationService::class);
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
        $this->dispatch('closeModal', $this->isNew ? $response : $response['data']);
    }

    public function delete(): void
    {
        if (! resolve_static(DeleteTranslation::class, 'canPerformAction', [false])) {
            return;
        }

        (new TranslationService)->delete($this->translation['id']);

        $this->skipRender();
        $this->dispatch('closeModal', $this->translation, true);
    }
}
