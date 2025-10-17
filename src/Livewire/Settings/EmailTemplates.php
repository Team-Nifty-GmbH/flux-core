<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Media\DeleteMedia;
use FluxErp\Contracts\OffersPrinting;
use FluxErp\Facades\EditorVariable;
use FluxErp\Livewire\DataTables\EmailTemplateList;
use FluxErp\Livewire\Forms\EmailTemplateForm;
use FluxErp\Models\EmailTemplate;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;
use FluxErp\Traits\Livewire\WithFilePond;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\Permission\Exceptions\UnauthorizedException;

class EmailTemplates extends EmailTemplateList
{
    use DataTableHasFormEdit, WithFilePond {
        DataTableHasFormEdit::save as baseSave;
        DataTableHasFormEdit::edit as baseEdit;
    }

    #[DataTableForm]
    public EmailTemplateForm $emailTemplateForm;

    public string $editorId;

    protected ?string $includeBefore = 'flux::livewire.settings.email-templates';

    public function mount(): void
    {
        parent::mount();
        $this->editorId = 'editor-' . uniqid();
    }

    public function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'modelTypes' => $this->getModelTypes(),
            ]
        );
    }

    public function updatedEmailTemplateFormModelType(): void
    {
        $this->skipRender();
        $variables = json_encode(EditorVariable::getTranslatedWithGlobals($this->emailTemplateForm->model_type));

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

    public function edit(string|int|null $id = null): void
    {
        $this->baseEdit($id);

        $this->updatedEmailTemplateFormModelType();
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
