<?php

namespace FluxErp\Http\Livewire\Settings;

use FluxErp\Models\EmailTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Livewire\Component;

class Emails extends Component
{
    public ?array $selectedEmailTemplate = null;

    public array $emailTemplateViews = [];

    public array $emailTemplates = [];

    public bool $editModal = false;

    protected $rules = [
        'selectedEmailTemplate' => 'required',
        'selectedEmailTemplate.name' => 'required',
        'selectedEmailTemplate.from' => 'required|email',
        'selectedEmailTemplate.from_alias' => 'required',
        'selectedEmailTemplate.to' => 'required',
        'selectedEmailTemplate.to.*' => 'required|email',
        'selectedEmailTemplate.cc' => 'required',
        'selectedEmailTemplate.cc.*' => 'required|email',
        'selectedEmailTemplate.bcc' => 'required',
        'selectedEmailTemplate.bcc.*' => 'required|email',
        'selectedEmailTemplate.view' => 'required',
    ];

    public function boot(): void
    {
        $this->emailTemplates = EmailTemplate::all()->toArray();

        $path = resource_path('views/emails');
        $emailTemplateFiles = \File::files($path);
        foreach ($emailTemplateFiles as $file) {
            $this->emailTemplateViews[] = $file->getFilename();
        }
    }

    public function render(): View
    {
        return view('flux::livewire.settings.emails');
    }

    /**
     * @param int|null $emailTemplateId
     */
    public function showEditModal(int|null $emailTemplateId = null): void
    {
        if (! $emailTemplateId) {
            $this->selectedEmailTemplate = [
                'id' => null,
                'view' => 'generic.blade.php',
                'can_overwrite_message' => true,
                'can_overwrite_receiver' => true,
                'can_overwrite_sender' => true,
                'can_overwrite_subject' => true,
            ];
        } else {
            $this->selectedEmailTemplate = EmailTemplate::find($emailTemplateId)->toArray();
        }

        $this->editModal = true;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        if (array_key_exists('to', $this->selectedEmailTemplate)) {
            $this->selectedEmailTemplate['to'] = $this->splitMails($this->selectedEmailTemplate['to']);
        }

        if (array_key_exists('cc', $this->selectedEmailTemplate)) {
            $this->selectedEmailTemplate['cc'] = $this->splitMails($this->selectedEmailTemplate['cc']);
        }

        if (array_key_exists('bcc', $this->selectedEmailTemplate)) {
            $this->selectedEmailTemplate['bcc'] = $this->splitMails($this->selectedEmailTemplate['bcc']);
        }

        $this->validate();

        $emailTemplate = EmailTemplate::query()->updateOrCreate(
            ['id' => $this->selectedEmailTemplate['id']],
            $this->selectedEmailTemplate
        );

        $this->selectedEmailTemplate = $emailTemplate->toArray();

        $key = array_key_first(
            Arr::where($this->emailTemplates, function ($emailTemplate) {
                return $emailTemplate['id'] === $this->selectedEmailTemplate['id'];
            })
        );

        if ($key === null) {
            $this->emailTemplates[] = $this->selectedEmailTemplate;
        } else {
            $this->emailTemplates[$key] = $this->selectedEmailTemplate;
        }

        $this->editModal = false;
    }

    private function splitMails($inputValue): array
    {
        if (is_array($inputValue)) {
            return $inputValue;
        }
        $mails = explode(',', $inputValue);
        $mails = array_map('trim', $mails);

        return array_filter($mails);
    }

    public function delete(): void
    {
        $collection = collect($this->emailTemplates);
        EmailTemplate::find($this->selectedEmailTemplate['id'])->delete();
        $this->emailTemplates = $collection->whereNotIn('id', [$this->selectedEmailTemplate['id']])->toArray();
    }
}
