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
use Illuminate\Database\Eloquent\Builder;
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
                ->label(__('Create'))
                ->color('primary')
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
                ->label(__('Edit'))
                ->color('primary')
                ->icon('pencil')
                ->attributes([
                    'x-on:click' => '$wire.edit(record.id)',
                ])
                ->when(fn () => resolve_static(UpdateMailAccount::class, 'canPerformAction', [false])),
            DataTableButton::make()
                ->label(__('Edit Folders'))
                ->color('primary')
                ->icon('pencil')
                ->attributes([
                    'x-on:click' => '$wire.editFolders(record.id)',
                ])
                ->when(fn () => resolve_static(UpdateMailFolder::class, 'canPerformAction', [false])),
            DataTableButton::make()
                ->label(__('Delete'))
                ->color('negative')
                ->icon('trash')
                ->attributes([
                    'wire:flux-confirm.icon.error' => __('wire:confirm.delete', ['model' => __('Mail Account')]),
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
            $openModal('edit-mail-account');
        JS);
    }

    public function editFolders(MailAccount $mailAccount): void
    {
        $this->mailAccount->reset();
        $this->mailAccount->fill($mailAccount);

        $this->loadFolders();

        $this->js(<<<'JS'
            $openModal('edit-mail-folders');
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

    public function testImapConnection(): void
    {
        try {
            $this->mailAccount->testImapConnection();

            $this->notification()->success(__('Connection successful'));
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

    public function testSmtpConnection(): void
    {
        try {
            $this->mailAccount->testSmtpConnection();

            $this->notification()->success(__('Connection successful'));
        } catch (
            ValidationException|TransportExceptionInterface $e
        ) {
            exception_to_notifications($e, $this);
        }
    }

    private function loadFolders(): void
    {
        app(MailFolder::class)->addGlobalScope('children', function (Builder $builder) {
            $builder->with('children')->where('mail_account_id', $this->mailAccount->id);
        });

        $this->folders = resolve_static(MailFolder::class, 'query')
            ->where('parent_id', null)
            ->get()
            ->toArray();
    }
}
