<?php

namespace FluxErp\Livewire;

use FluxErp\Livewire\Forms\CommunicationForm;
use FluxErp\Mail\GenericMail;
use FluxErp\Models\Media;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Mail;
use Laravel\SerializableClosure\SerializableClosure;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use WireUi\Traits\Actions;

class EditMail extends Component
{
    use Actions, WithFileUploads;

    public CommunicationForm $mailMessage;

    public array $files = [];

    public array $mailMessages = [];

    public bool $multiple = false;

    public ?string $sessionKey = null;

    protected $listeners = [
        'create',
        'createMany',
        'createFromSession',
    ];

    public function render(): View
    {
        return view('flux::livewire.edit-mail', [
            'mailAccounts' => array_merge(
                auth()
                    ->user()
                    ->mailAccounts()
                    ->whereNotNull([
                        'smtp_email',
                        'smtp_password',
                        'smtp_host',
                        'smtp_port',
                    ])
                    ->get(['mail_accounts.id', 'email'])
                    ->toArray(),
                [
                    ['id' => null, 'email' => __('Default')],
                ]
            ),
        ]);
    }

    #[Renderless]
    public function create(array|CommunicationForm|Model $values): void
    {
        $this->multiple = false;
        $this->sessionKey = null;
        $this->reset('mailMessages');

        if ($values instanceof Model || is_array($values)) {
            $this->mailMessage->fill($values);
        } else {
            $this->mailMessage = $values;
        }

        $this->js(<<<'JS'
            $openModal('edit-mail');
        JS);
    }

    #[Renderless]
    public function createMany(Collection|array $mailMessages): void
    {
        $sessionKey = $this->sessionKey;
        $this->create($mailMessages[0]);
        $this->sessionKey = $sessionKey;
        $this->mailMessage->reset('attachments');

        if (! $sessionKey) {
            $this->mailMessages = $mailMessages;
        }

        if (count($mailMessages) > 1) {
            $this->multiple = true;
        }
    }

    #[Renderless]
    public function createFromSession(string $key): void
    {
        $data = session()->get($key);

        if (Arr::isAssoc($data) || count($data) === 1) {
            $data = count($data) === 1 && Arr::isList($data) ? $data[0] : $data;
            session()->forget($key);

            $bladeParameters = $this->getBladeParameters($data);
            $data['blade_parameters_serialized'] = false;
            $data['blade_parameters'] = null;

            $data = array_merge(
                $data,
                [
                    'subject' => Blade::render(
                        data_get($data, 'subject'),
                        $bladeParameters instanceof SerializableClosure
                            ? $bladeParameters->getClosure()()
                            : []
                    ),
                    'html_body' => Blade::render(
                        data_get($data, 'html_body'),
                        $bladeParameters instanceof SerializableClosure
                            ? $bladeParameters->getClosure()()
                            : []
                    ),
                    'text_body' => Blade::render(
                        data_get($data, 'text_body'),
                        $bladeParameters instanceof SerializableClosure
                            ? $bladeParameters->getClosure()()
                            : []
                    ),
                ]
            );
            $this->create($data);
        } else {
            $this->createMany($data);
            $this->sessionKey = $key;
        }
    }

    #[Renderless]
    public function updatedFiles(): void
    {
        $files = array_map(function ($file) {
            /** @var TemporaryUploadedFile $file */
            return [
                'name' => $file->getClientOriginalName(),
                'path' => $file->getRealPath(),
            ];
        }, $this->files);

        $this->mailMessage->attachments = array_merge($this->mailMessage->attachments, $files);
    }

    #[Renderless]
    public function send(): bool
    {
        $editedMailMessage = $this->mailMessage->toArray();
        if (! $this->mailMessages && ! $this->sessionKey) {
            $this->mailMessages = [$this->mailMessage];
        } else {
            $this->mailMessages = $this->mailMessages ?: session()->pull($this->sessionKey);
        }

        $single = count($this->mailMessages) === 1;
        if (! $single) {
            unset($editedMailMessage['to']);
        }

        $bcc = $this->mailMessage->bcc;
        $cc = $this->mailMessage->cc;
        $exceptions = 0;

        foreach ($this->mailMessages as $mailMessage) {
            $bladeParameters = $this->getBladeParameters($mailMessage);

            if (! $mailMessage instanceof CommunicationForm) {
                $this->mailMessage->reset();

                $this->mailMessage->fill(array_merge(
                    $mailMessage,
                    array_filter($editedMailMessage),
                    [
                        'bcc' => $bcc,
                    ]
                ));
            }

            $mail = GenericMail::make($this->mailMessage, $bladeParameters);
            try {
                $message = Mail::to($this->mailMessage->to)
                    ->cc($cc)
                    ->bcc($bcc);

                if ($single) {
                    $message->send($mail);
                } else {
                    $message->queue($mail);
                }

            } catch (\Exception $e) {
                exception_to_notifications(
                    exception: $e,
                    component: $this,
                    description: $this->mailMessage->subject
                );

                if ($this->multiple) {
                    $exceptions++;

                    continue;
                }

                return false;
            }
        }

        if ($exceptions === 0) {
            $this->notification()->success(__('Email(s) sent successfully!'));
        }

        if (count($this->mailMessages) === $exceptions) {
            $this->notification()->error(__('Failed to send emails!'));
        }

        return true;
    }

    #[Renderless]
    public function downloadAttachment(Media $media): BinaryFileResponse
    {
        return response()->download($media->getPath());
    }

    #[Renderless]
    public function clear(): void
    {
        $this->mailMessage->reset();

        $this->cleanupOldUploads();
    }

    protected function getBladeParameters(array|CommunicationForm $mailMessage): array|SerializableClosure|null
    {
        $bladeParameters = data_get($mailMessage, 'blade_parameters');

        if (data_get($mailMessage, 'blade_parameters_serialized') && is_string($bladeParameters)) {
            $bladeParameters = unserialize($bladeParameters);
        }

        return $bladeParameters;
    }
}
