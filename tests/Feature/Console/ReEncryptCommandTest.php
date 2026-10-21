<?php

use FluxErp\Models\MailAccount;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $this->oldKey = Encrypter::generateKey(config('app.cipher'));
    $this->oldEncrypter = new Encrypter($this->oldKey, config('app.cipher'));

    $this->currentEncrypter = new Encrypter(app('encrypter')->getKey(), config('app.cipher'));

    $this->mailAccount = MailAccount::factory()->create();

    config(['app.previous_keys' => ['base64:' . base64_encode($this->oldKey)]]);
    app()->forgetInstance('encrypter');
    Crypt::clearResolvedInstance('encrypter');
});

function rawPassword(MailAccount $mailAccount): ?string
{
    return DB::table('mail_accounts')->where('id', $mailAccount->getKey())->value('password');
}

function setRawPassword(MailAccount $mailAccount, ?string $value): void
{
    DB::table('mail_accounts')->where('id', $mailAccount->getKey())->update(['password' => $value]);
}

test('re encrypts a model value written with a previous key', function (): void {
    setRawPassword($this->mailAccount, $this->oldEncrypter->encryptString('secret'));
    $updatedAt = DB::table('mail_accounts')->where('id', $this->mailAccount->getKey())->value('updated_at');

    $this->artisan('flux:re-encrypt')->assertSuccessful();

    expect($this->currentEncrypter->decryptString(rawPassword($this->mailAccount)))->toBe('secret')
        ->and(DB::table('mail_accounts')->where('id', $this->mailAccount->getKey())->value('updated_at'))
        ->toBe($updatedAt);
});

test('dry run counts previous key values without writing', function (): void {
    setRawPassword($this->mailAccount, $raw = $this->oldEncrypter->encryptString('secret'));

    $this->artisan('flux:re-encrypt', ['--dry-run' => true])
        ->expectsOutputToContain('mail_accounts.password: 1 with a previous key')
        ->assertSuccessful();

    expect(rawPassword($this->mailAccount))->toBe($raw);
});

test('leaves a value alone that is already on the current key', function (): void {
    setRawPassword($this->mailAccount, $raw = $this->currentEncrypter->encryptString('secret'));

    $this->artisan('flux:re-encrypt')->assertSuccessful();

    expect(rawPassword($this->mailAccount))->toBe($raw);
});

test('reports a value no key can decrypt and keeps it', function (): void {
    $foreign = (new Encrypter(Encrypter::generateKey(config('app.cipher')), config('app.cipher')))
        ->encryptString('secret');
    setRawPassword($this->mailAccount, $foreign);

    $this->artisan('flux:re-encrypt')
        ->expectsOutputToContain('mail_accounts.password id ' . $this->mailAccount->getKey())
        ->assertFailed();

    expect(rawPassword($this->mailAccount))->toBe($foreign);
});

test('re encrypts an encrypted settings property', function (): void {
    DB::table('settings')->updateOrInsert(
        ['group' => 'mail', 'name' => 'password'],
        ['payload' => json_encode($this->oldEncrypter->encrypt('smtp-secret')), 'locked' => false]
    );

    $this->artisan('flux:re-encrypt')->assertSuccessful();

    $payload = json_decode(
        DB::table('settings')->where('group', 'mail')->where('name', 'password')->value('payload')
    );

    expect($this->currentEncrypter->decrypt($payload))->toBe('smtp-secret');
});
