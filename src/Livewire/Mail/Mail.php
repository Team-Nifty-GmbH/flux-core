<?php

namespace FluxErp\Livewire\Mail;

use FluxErp\Jobs\SyncMailAccountJob;
use FluxErp\Livewire\DataTables\MailMessageList;
use FluxErp\Livewire\Forms\MailMessageForm as MailMessageForm;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use FluxErp\Models\MailMessage;
use FluxErp\Models\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Livewire\Attributes\Locked;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Mail extends MailMessageList
{
    protected string $view = 'flux::livewire.mail.mail';

    public array $folders = [];

    public ?string $folderId = null;

    public array $selectedFolderIds = [];

    #[Locked]
    public array $mailAccounts = [];

    public MailMessageForm $mailMessage;

    public function mount(): void
    {
        parent::mount();
        $this->mailAccounts = auth()
            ->user()
            ->mailAccounts()
            ->get(['mail_accounts.id', 'uuid', 'email'])
            ?->toArray() ?? [];

        MailFolder::addGlobalScope('children', function (Builder $builder) {
            $builder->with('children');
        });

        $this->folders[] = [
            'id' => null,
            'name' => __('All Messages'),
            'children' => [],
        ];

        foreach ($this->mailAccounts as $mailAccount) {
            $mailFolders = MailFolder::query()
                ->where('parent_id', null)
                ->where('mail_account_id', $mailAccount['id'])
                ->get(['id', 'name', 'parent_id']);

            $this->folders[] = [
                'id' => $mailAccount['uuid'],
                'name' => $mailAccount['email'],
                'children' => $mailFolders->toArray(),
            ];
        }
    }

    public function showMail(MailMessage $message): void
    {
        $this->skipRender();
        $this->mailMessage->fill($message);
        $this->js(<<<'JS'
            writeHtml();
            $openModal('show-mail');
        JS);
    }

    public function download(Media $mediaItem): false|BinaryFileResponse
    {
        if (! file_exists($mediaItem->getPath())) {
            if (method_exists($this, 'notification')) {
                $this->notification()->error(__('File not found!'));
            }

            return false;
        }

        return response()->download($mediaItem->getPath(), $mediaItem->file_name);
    }

    public function updatedFolderId(): void
    {
        if (is_null($this->folderId)) {
            $this->selectedFolderIds = [];
        } elseif (! is_numeric($this->folderId)) {
            $folderTree = data_get(Arr::keyBy($this->folders, 'id'), $this->folderId . '.children', []);
            $this->selectedFolderIds = array_column(to_flat_tree($folderTree), 'id');
        } else {
            $folderTree = $this->findFolderIdById($this->folders, $this->folderId)['children'] ?? [];
            $this->selectedFolderIds = array_merge(
                [$this->folderId],
                array_column(to_flat_tree($folderTree), 'id')
            );
        }

        $this->search = '';

        $this->updatedSearch();
    }

    public function getNewMessages(): void
    {
        $mailAccounts = MailAccount::query()
            ->whereIntegerInRaw('id', array_column($this->mailAccounts, 'id'))
            ->get();
        foreach ($mailAccounts as $mailAccount) {
            SyncMailAccountJob::dispatch($mailAccount);
        }
    }

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->whereIntegerInRaw('mail_account_id', array_column($this->mailAccounts, 'id'))
            ->when($this->folderId, function (Builder $builder) {
                $builder->whereIntegerInRaw('mail_folder_id', $this->selectedFolderIds);
            });
    }

    private function findFolderIdById($folders, $id): ?array
    {
        foreach ($folders as $element) {
            if (($element['id'] ?? false) && $element['id'] == $id) {
                return $element;
            }

            if (($element['children'] ?? false) && is_array($element['children'])) {
                $found = $this->findFolderIdById($element['children'], $id);
                if ($found) {
                    return $found;
                }
            }
        }

        return null;
    }
}
