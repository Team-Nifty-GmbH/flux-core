<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Media\DeleteMedia;
use FluxErp\Livewire\DataTables\EmailTemplateList;
use FluxErp\Livewire\Forms\EmailTemplateForm;
use FluxErp\Models\EmailTemplate;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;
use FluxErp\Traits\Livewire\WithFilePond;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\Permission\Exceptions\UnauthorizedException;

class EmailTemplates extends EmailTemplateList
{
    use DataTableHasFormEdit, WithFilePond {
        DataTableHasFormEdit::save as baseSave;
    }

    #[DataTableForm]
    public EmailTemplateForm $emailTemplateForm;

    protected ?string $includeBefore = 'flux::livewire.settings.email-templates';

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
}
