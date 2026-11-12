<?php

use FluxErp\Actions\Address\UpdateAddress;

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

test('action input keeps whitespace of excluded keys', function (): void {
    $data = UpdateAddress::make([
        'password' => ' secret ',
        'password_confirmation' => ' secret ',
        'current_password' => ' old ',
        'smtp_password' => ' smtp ',
        'prefix' => 'RE ',
        'suffix' => ' /26',
        'html_body' => "<p>a</p>\n",
        'text_body' => "  indented\n",
        'nested' => ['password' => ' secret '],
    ])->getData();

    expect($data)
        ->password->toBe(' secret ')
        ->password_confirmation->toBe(' secret ')
        ->current_password->toBe(' old ')
        ->smtp_password->toBe(' smtp ')
        ->prefix->toBe('RE ')
        ->suffix->toBe(' /26')
        ->html_body->toBe("<p>a</p>\n")
        ->text_body->toBe("  indented\n")
        ->and($data['nested']['password'])->toBe(' secret ');
});
