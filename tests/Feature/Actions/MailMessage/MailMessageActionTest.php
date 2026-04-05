<?php

use FluxErp\Actions\MailFolder\CreateMailFolder;
use FluxErp\Actions\MailMessage\CreateMailMessage;
use FluxErp\Models\MailAccount;

beforeEach(function (): void {
    $this->mailAccount = MailAccount::factory()->create();
    $this->mailFolder = CreateMailFolder::make([
        'mail_account_id' => $this->mailAccount->getKey(),
        'name' => 'Inbox',
        'slug' => 'INBOX',
    ])->validate()->execute();
});

test('create mail message', function (): void {
    $message = CreateMailMessage::make([
        'mail_folder_id' => $this->mailFolder->getKey(),
        'mail_account_id' => $this->mailAccount->getKey(),
        'from' => 'test@example.com',
        'subject' => 'Test Email',
        'text_body' => 'Hello World',
        'communication_type_enum' => 'mail',
    ])->validate()->execute();

    expect($message)->subject->toBe('Test Email');
});
