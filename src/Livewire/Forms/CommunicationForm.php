<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Communication\CreateCommunication;
use FluxErp\Actions\Communication\DeleteCommunication;
use FluxErp\Actions\Communication\UpdateCommunication;
use FluxErp\Models\Communication;
use FluxErp\Models\Pivots\Communicatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;

class CommunicationForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?int $mail_account_id = null;

    public ?int $mail_folder_id = null;

    public ?string $from = null;

    public ?array $to = [];

    public ?array $cc = [];

    public ?array $bcc = [];

    public ?string $communication_type_enum = null;

    public ?string $date = null;

    public ?string $subject = null;

    public ?string $html_body = null;

    public ?string $text_body = null;

    public array $attachments = [];

    public ?string $slug = null;

    public array $tags = [];

    public array $communicatables = [];

    protected function getActions(): array
    {
        return [
            'create' => CreateCommunication::class,
            'update' => UpdateCommunication::class,
            'delete' => DeleteCommunication::class,
        ];
    }

    public function fill($values): void
    {
        if ($values instanceof Communication) {
            $values->loadMissing(['tags:id', 'communicatables']);
            $values->communicatables->map(function (Communicatable $communicatable) {
                $communicatable->href = method_exists($communicatable->communicatable, 'getUrl')
                    ? $communicatable->communicatable->getUrl()
                    : null;

                $typeLabel = __(Str::headline($communicatable->communicatable_type));
                $modelLabel = method_exists($communicatable->communicatable, 'getLabel')
                    ? $communicatable->communicatable->getLabel()
                    : null;

                $communicatable->label = $modelLabel ? $typeLabel . ': ' . $modelLabel : $typeLabel;

                $communicatable->unsetRelation('communicatable');
            });

            $values = $values->toArray();
            $values['tags'] = array_column($values['tags'] ?? [], 'id');
        }

        parent::fill($values);

        $this->to ??= [];
        $this->cc ??= [];
        $this->bcc ??= [];

        if ($this->id) {
            $message = $values instanceof Communication
                ? $values->load(['mailFolder:id,slug', 'mailAccount:id,email'])
                : resolve_static(Communication::class, 'query')
                    ->whereKey($this->id)
                    ->with(['mailFolder:id,slug', 'mailAccount:id,email'])
                    ->first();

            $this->attachments = $message
                ->getMedia('attachments')
                ->map(fn ($media) => [
                    'id' => $media->id,
                    'name' => $media->name,
                ])
                ->toArray();

            $this->slug = $message->mailAccount ?
                $message->mailAccount->email . ' -> ' . $message->mailFolder?->slug : null;
        }
    }

    public function communicatable(): ?Model
    {
        return $this->communicatable_type && $this->communicatable_id
            ? morphed_model($this->communicatable_type)::query()
                ->whereKey($this->communicatable_id)
                ->first()
            : null;
    }
}
