<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\MailAccount\CreateMailAccount;
use FluxErp\Actions\MailAccount\DeleteMailAccount;
use FluxErp\Actions\MailAccount\UpdateMailAccount;
use FluxErp\Actions\MailFolder\UpdateMailFolder;
use FluxErp\Jobs\SyncMailAccountJob;
use FluxErp\Livewire\DataTables\MailAccountList;
use FluxErp\Livewire\Forms\MailAccountForm;
use FluxErp\Livewire\Forms\MailFolderForm;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use Webklex\PHPIMAP\Exceptions\AuthFailedException;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;
use Webklex\PHPIMAP\Exceptions\ImapBadRequestException;
use Webklex\PHPIMAP\Exceptions\ImapServerErrorException;
use Webklex\PHPIMAP\Exceptions\ResponseException;
use Webklex\PHPIMAP\Exceptions\RuntimeException;

#[Renderless]
class MailAccounts extends MailAccountList
{
    protected ?string $includeBefore = 'flux::livewire.settings.mail-accounts';

    public MailAccountForm $mailAccount;

    public MailFolderForm $mailFolder;

    public array $folders = [];

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Create'))
                ->color('indigo')
                ->icon('plus')
                ->attributes([
                    'x-on:click' => '$wire.edit()',
                ])
                ->when(fn () => resolve_static(CreateMailAccount::class, 'canPerformAction', [false])),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->color('indigo')
                ->icon('pencil')
                ->attributes([
                    'x-on:click' => '$wire.edit(record.id)',
                ])
                ->when(fn () => resolve_static(UpdateMailAccount::class, 'canPerformAction', [false])),
            DataTableButton::make()
                ->text(__('Edit Folders'))
                ->color('indigo')
                ->icon('pencil')
                ->attributes([
                    'x-on:click' => '$wire.editFolders(record.id)',
                ])
                ->when(fn () => resolve_static(UpdateMailFolder::class, 'canPerformAction', [false])),
            DataTableButton::make()
                ->text(__('Delete'))
                ->color('red')
                ->icon('trash')
                ->attributes([
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Mail Account')]),
                    'wire:click' => 'delete(record.id)',
                ])
                ->when(fn () => resolve_static(DeleteMailAccount::class, 'canPerformAction', [false])),
        ];
    }

    public function edit(MailAccount $mailAccount): void
    {
        $this->mailAccount->reset();
        $this->mailAccount->fill($mailAccount);

        $this->js(<<<'JS'
            $modalOpen('edit-mail-account');
        JS);
    }

    public function editFolders(MailAccount $mailAccount): void
    {
        $this->mailAccount->reset();
        $this->mailAccount->fill($mailAccount);

        $this->loadFolders();

        $this->js(<<<'JS'
            $modalOpen('edit-mail-folders');
        JS);
    }

    public function editMailFolder(MailFolder $mailFolder): void
    {
        $this->mailFolder->reset();
        $this->mailFolder->fill($mailFolder);
    }

    public function saveMailFolder(): void
    {
        try {
            $this->mailFolder->save();
            $this->mailFolder->reset();
            $this->loadFolders();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);
        }
    }

    public function syncFolders(MailAccount $mailAccount): void
    {
        SyncMailAccountJob::dispatchSync($mailAccount, true);

        $this->editFolders($mailAccount);
    }

    public function save(): bool
    {
        try {
            $this->mailAccount->save();
            $this->mailAccount->reset();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function delete(int $id): bool
    {
        try {
            DeleteMailAccount::make(['id' => $id])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function testImapConnection(): void
    {
        try {
            $this->mailAccount->testImapConnection();

            $this->notification()->success(__('Connection successful'))->send();
        } catch (
            ValidationException
            |ImapBadRequestException
            |RuntimeException
            |ResponseException
            |ConnectionFailedException
            |AuthFailedException
            |ImapServerErrorException $e
        ) {
            exception_to_notifications($e, $this);
        }
    }

    #[Renderless]
    public function testSmtpConnection(): void
    {
        try {
            $this->mailAccount->testSmtpConnection();

            $this->notification()->success(__('Connection successful'))->send();
        } catch (ValidationException|TransportExceptionInterface $e) {
            exception_to_notifications($e, $this);
        }
    }

    #[Renderless]
    public function sendTestMail(?string $to = null): void
    {
        try {
            if (! is_null($to)) {
                Validator::make(['to' => $to], ['to' => 'required|email'])
                    ->validate();
            }

            $this->mailAccount->sendTestMail($to);

            $this->notification()->success(__('Test mail sent'))->send();
        } catch (ValidationException|TransportExceptionInterface $e) {
            exception_to_notifications($e, $this);
        }
    }

    protected function loadFolders(): void
    {
        $this->folders = resolve_static(MailFolder::class, 'familyTree')
            ->where('parent_id', null)
            ->where('mail_account_id', $this->mailAccount->id)
            ->get()
            ->toArray();
    }
}
