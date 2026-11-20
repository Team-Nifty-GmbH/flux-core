<?php

use FluxErp\Actions\Address\CreateAddress;
use FluxErp\Actions\Address\UpdateAddress;
use FluxErp\Actions\Communication\CreateCommunication;
use FluxErp\Actions\Communication\UpdateCommunication;
use FluxErp\Actions\EmailTemplate\CreateEmailTemplate;
use FluxErp\Actions\EmailTemplate\UpdateEmailTemplate;
use FluxErp\Actions\MailAccount\CreateMailAccount;
use FluxErp\Actions\MailAccount\UpdateMailAccount;
use FluxErp\Actions\MailMessage\CreateMailMessage;
use FluxErp\Actions\MailMessage\SendMail;
use FluxErp\Actions\SerialNumberRange\CreateSerialNumberRange;
use FluxErp\Actions\SerialNumberRange\UpdateSerialNumberRange;
use FluxErp\Actions\User\CreateUser;
use FluxErp\Actions\User\UpdateUser;

test('action input strings are trimmed recursively including invisible characters', function (): void {
    $data = UpdateAddress::make([
        'company' => "  Acme\u{00A0}GmbH \u{FEFF}",
        'contact_options' => [
            ['value' => "\u{200B}\tfoo bar\n"],
        ],
    ])->getData();

    expect($data['company'])->toBe("Acme\u{00A0}GmbH")
        ->and($data['contact_options'][0]['value'])->toBe('foo bar');
});

test('action input strings of only whitespace become null', function (): void {
    expect(UpdateAddress::make(['company' => " \u{200B} "])->getData('company'))->toBeNull();
});

test('action input strings of only whitespace stay empty when empty strings are kept', function (): void {
    expect(UpdateAddress::make(['company' => '  '], true)->getData('company'))->toBe('');
});

test('action input trims keys another action excludes', function (): void {
    expect(UpdateAddress::make([
        'prefix' => 'RE ',
        'html_body' => "<p>a</p>\n",
        'smtp_password' => ' smtp ',
    ])->getData())
        ->prefix->toBe('RE')
        ->html_body->toBe('<p>a</p>')
        ->smtp_password->toBe('smtp');
});

test('action input keeps whitespace of keys the action excludes', function (string $action, array $keys): void {
    $data = array_fill_keys($keys, " value\n");

    expect($action::make($data)->getData())->toBe($data);
})->with([
    [CreateUser::class, ['password']],
    [UpdateUser::class, ['password']],
    [CreateAddress::class, ['password']],
    [UpdateAddress::class, ['password']],
    [CreateMailAccount::class, ['password', 'smtp_password']],
    [UpdateMailAccount::class, ['password', 'smtp_password']],
    [CreateEmailTemplate::class, ['html_body', 'text_body']],
    [UpdateEmailTemplate::class, ['html_body', 'text_body']],
    [CreateCommunication::class, ['html_body', 'text_body']],
    [UpdateCommunication::class, ['html_body', 'text_body']],
    [CreateMailMessage::class, ['html_body', 'text_body']],
    [SendMail::class, ['html_body', 'text_body']],
    [CreateSerialNumberRange::class, ['prefix', 'suffix']],
    [UpdateSerialNumberRange::class, ['prefix', 'suffix']],
]);

test('action input keeps whitespace of excluded keys in nested arrays', function (): void {
    expect(UpdateAddress::make(['nested' => ['password' => ' secret ']])->getData('nested.password'))
        ->toBe(' secret ');
});
