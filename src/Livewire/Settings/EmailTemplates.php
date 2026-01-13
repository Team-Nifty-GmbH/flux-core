<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Media\DeleteMedia;
use FluxErp\Contracts\OffersPrinting;
use FluxErp\Facades\Editor;
use FluxErp\Livewire\DataTables\EmailTemplateList;
use FluxErp\Livewire\Forms\EmailTemplateForm;
use FluxErp\Models\EmailTemplate;
use FluxErp\Models\Language;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTable\DataTableHasFormEdit;
use FluxErp\Traits\Livewire\WithFilePond;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\Permission\Exceptions\UnauthorizedException;

class EmailTemplates extends EmailTemplateList
{
    use DataTableHasFormEdit, WithFilePond {
        DataTableHasFormEdit::save as baseSave;
        DataTableHasFormEdit::edit as baseEdit;
    }

    #[Locked]
    public string $editorId;

    public ?int $languageId = null;

    public array $languages = [];

    #[DataTableForm]
    public EmailTemplateForm $emailTemplateForm;

    protected ?string $includeBefore = 'flux::livewire.settings.email-templates';

    public function mount(): void
    {
        parent::mount();
        $this->editorId = 'editor-' . uniqid();

        $this->languageId = Session::get('selectedLanguageId')
            ?? resolve_static(Language::class, 'default')?->getKey();

        $this->languages = resolve_static(Language::class, 'query')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    public function edit(string|int|null $id = null): void
    {
        $this->baseEdit($id);

        $this->updatedEmailTemplateFormModelType();
    }

    public function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'languages' => $this->languages,
                'modelTypes' => $this->getModelTypes(),
            ]
        );
    }

    public function localize(): void
    {
        Session::put('selectedLanguageId', $this->languageId);

        if ($this->emailTemplateForm->id) {
            $this->emailTemplateForm->fill(
                resolve_static(EmailTemplate::class, 'query')
                    ->whereKey($this->emailTemplateForm->id)
                    ->first()
            );
        }
    }

    #[Renderless]
    public function save(): bool
    {
        $result = $this->baseSave();

        if ($result) {
            $this->submitFiles(
                'default',
                collect($this->files)
                    ->map(fn (TemporaryUploadedFile $file) => $file->getFilename())
                    ->toArray(),
                morph_alias(EmailTemplate::class),
                $this->emailTemplateForm->id
            );

            $this->dispatch('clear-pond');

            foreach ($this->emailTemplateForm->deleteMedia as $mediaId) {
                try {
                    DeleteMedia::make(['id' => $mediaId])
                        ->checkPermission()
                        ->validate()
                        ->execute();
                } catch (ValidationException|UnauthorizedException $e) {
                    exception_to_notifications($e, $this);
                }
            }
        }

        return $result;
    }

    public function updatedEmailTemplateFormModelType(): void
    {
        $this->skipRender();
        $variables = json_encode(Editor::getTranslatedVariables($this->emailTemplateForm->model_type));

        $this->js(<<<JS
            const editorElement = document.querySelector('[x-ref="editor-{$this->editorId}"]');
            if (editorElement) {
                const alpineData = Alpine.\$data(editorElement.closest('[x-data*="setupEditor"]'));
                if (alpineData) {
                    alpineData.bladeVariables = $variables;
                }
            }
        JS);
    }

    protected function getModelTypes(): array
    {
        $modelTypes = [
            [
                'value' => null,
                'label' => __('General'),
            ],
        ];

        foreach (Relation::morphMap() as $key => $modelClass) {
            if (is_a(resolve_static($modelClass, 'class'), OffersPrinting::class, true)) {
                $modelTypes[] = [
                    'value' => $key,
                    'label' => __(Str::headline($key)),
                ];
            }
        }

        return $modelTypes;
    }
}
