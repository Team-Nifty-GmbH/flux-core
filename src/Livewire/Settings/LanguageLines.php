<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\LanguageLine\CreateLanguageLine;
use FluxErp\Actions\LanguageLine\DeleteLanguageLine;
use FluxErp\Actions\LanguageLine\UpdateLanguageLine;
use FluxErp\Livewire\DataTables\LanguageLineList;
use FluxErp\Livewire\Forms\LanguageLineForm;
use FluxErp\Models\Language;
use FluxErp\Models\LanguageLine;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class LanguageLines extends LanguageLineList
{
    use Actions;

    public LanguageLineForm $languageLineForm;

    protected ?string $includeBefore = 'flux::livewire.settings.language-lines';

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Create'))
                ->color('indigo')
                ->icon('plus')
                ->when(resolve_static(CreateLanguageLine::class, 'canPerformAction', [false]))
                ->wireClick('edit'),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->icon('pencil')
                ->color('indigo')
                ->when(resolve_static(UpdateLanguageLine::class, 'canPerformAction', [false]))
                ->wireClick('edit(record.id)'),
            DataTableButton::make()
                ->text(__('Delete'))
                ->color('red')
                ->icon('trash')
                ->when(resolve_static(DeleteLanguageLine::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Language Line')]),
                ]),
        ];
    }

    #[Renderless]
    public function delete(LanguageLine $languageLine): bool
    {
        $this->languageLineForm->reset();
        $this->languageLineForm->fill($languageLine);

        try {
            $this->languageLineForm->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function edit(LanguageLine $languageLine): void
    {
        $this->languageLineForm->reset();
        $this->languageLineForm->fill($languageLine);

        $this->js(<<<'JS'
            $modalOpen('edit-language-line-modal');
        JS);
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->languageLineForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'locales' => resolve_static(Language::class, 'query')
                    ->select('language_code')
                    ->distinct()
                    ->pluck('language_code')
                    ->toArray(),
            ]
        );
    }
}
