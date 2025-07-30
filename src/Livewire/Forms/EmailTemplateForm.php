<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\EmailTemplate\CreateEmailTemplate;
use FluxErp\Actions\EmailTemplate\DeleteEmailTemplate;
use FluxErp\Actions\EmailTemplate\UpdateEmailTemplate;
use FluxErp\Models\EmailTemplate;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Livewire\Attributes\Locked;

class EmailTemplateForm extends FluxForm
{
    use SupportsAutoRender;

    public ?array $bcc = null;

    public ?array $cc = null;

    public array $deleteMedia = [];

    public ?string $html_body = null;

    #[Locked]
    public ?int $id = null;

    public ?array $media = null;

    public ?string $model_type = null;

    public ?string $name = null;

    public ?string $subject = null;

    public ?string $text_body = null;

    public ?array $to = null;

    public function fill($values): void
    {
        if ($values instanceof EmailTemplate) {
            $this->media = $values->getMedia()->toArray();
        }

        parent::fill($values);
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateEmailTemplate::class,
            'update' => UpdateEmailTemplate::class,
            'delete' => DeleteEmailTemplate::class,
        ];
    }
}
