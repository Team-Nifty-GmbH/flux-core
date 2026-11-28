<?php

use FluxErp\Models\Communication;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;

test('the mail preview shows the html body without running it', function (): void {
    $mailAccount = MailAccount::factory()
        ->has(MailFolder::factory())
        ->create();
    $this->user->mailAccounts()->attach($mailAccount->getKey());

    $message = Communication::factory()->create([
        'mail_account_id' => $mailAccount->getKey(),
        'mail_folder_id' => $mailAccount->mailFolders->first()->getKey(),
        'communication_type_enum' => 'mail',
        'subject' => 'Newsletter',
        'html_body' => '<h1>Newsletter Ausgabe</h1><img src="x" onerror="window.parent.mailPreviewRan = true">',
    ]);

    $page = visit(route('mail'))->assertNoSmoke();

    $page->script(<<<JS
        () => new Promise((resolve) => {
            const open = () => {
                const component = Livewire.all().find((c) => c.name === 'mail.mail');

                if (! component) {
                    setTimeout(open, 200);

                    return;
                }

                component.\$wire.showMail({$message->getKey()}).then(resolve);
            };
            open();
        })
    JS);

    $page->wait(1);

    expect($page->script('() => window.mailPreviewRan === true'))->toBeFalse()
        ->and($page->script('() => document.getElementById("mail-body").getAttribute("sandbox")'))
        ->toBe('allow-popups allow-popups-to-escape-sandbox')
        ->and($page->script('() => document.getElementById("mail-body").getAttribute("srcdoc") ?? ""'))
        ->toContain('<h1>Newsletter Ausgabe</h1>');
});
