<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\MailAccount\CreateMailAccount;
use FluxErp\Actions\MailAccount\DeleteMailAccount;
use FluxErp\Actions\MailAccount\UpdateMailAccount;
use FluxErp\Livewire\DataTables\MailAccountList;
use FluxErp\Livewire\Forms\MailAccountForm as MailAccountForm;
use FluxErp\Models\MailAccount;
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
    protected string $view = 'flux::livewire.settings.mail-accounts';

    public MailAccountForm $mailAccount;

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create'))
                ->color('primary')
                ->icon('plus')
                ->attributes([
                    'x-on:click' => '$wire.edit()',
                ])
                ->when(fn () => CreateMailAccount::canPerformAction(false)),
        ];
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->color('primary')
                ->icon('pencil')
                ->attributes([
                    'x-on:click' => '$wire.edit(record.id)',
                ])
                ->when(fn () => UpdateMailAccount::canPerformAction(false)),
            DataTableButton::make()
                ->label(__('Delete'))
                ->color('negative')
                ->icon('trash')
                ->attributes([
                    'x-on:click' => 'deleteDialog(record.id)',
                ])
                ->when(fn () => DeleteMailAccount::canPerformAction(false)),
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
                ->validate()
                ->checkPermission()
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
}
