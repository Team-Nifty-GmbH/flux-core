<?php

use FluxErp\Actions\MailMessage\SendMail;
use FluxErp\Mail\GenericMail;
use FluxErp\Models\EmailTemplate;
use FluxErp\Models\MailAccount;
use FluxErp\Models\Tenant;
use Illuminate\Support\Facades\Mail;

test('handle mail failure', function (): void {
    Mail::shouldReceive('mailer')->andReturnSelf();
    Mail::shouldReceive('to')->andReturnSelf();
    Mail::shouldReceive('cc')->andReturnSelf();
    Mail::shouldReceive('bcc')->andReturnSelf();
    Mail::shouldReceive('send')->andThrow(new Exception('Mail server error'));

    $tenant = Tenant::factory()->create();

    $action = SendMail::make([
        'tenant_id' => $tenant->id,
        'to' => ['test@example.com'],
        'subject' => 'Test Subject',
        'text_body' => 'Test Text Body',
        'html_body' => '<p>Test Body</p>',
        'queue' => false,
    ]);

    $result = $action->validate()->execute();

    expect($result['success'])->toBeFalse();
    expect($result['message'])->toEqual(__('Failed to send email!'));
    expect($result['error'])->toEqual('Mail server error');
});

test('handles null values correctly', function (): void {
    Mail::fake();

    $template = EmailTemplate::factory()->create([
        'subject' => 'Template Subject',
        'html_body' => '<p>Template Body</p>',
        'to' => ['template@example.com'],
        'cc' => ['template-cc@example.com'],
        'bcc' => ['template-bcc@example.com'],
    ]);

    $action = SendMail::make([
        'to' => ['user@example.com'],
        'subject' => 'User Subject',
        'html_body' => '<p>User Body</p>',
        'cc' => null,
        'bcc' => null,
        'attachments' => null,
        'template_id' => $template->id,
    ]);

    $result = $action->validate()->execute();

    expect($result['success'])->toBeTrue();

    Mail::assertSent(GenericMail::class, function ($mail) {
        return $mail->hasTo('user@example.com')
            && $mail->hasCc('template-cc@example.com')
            && $mail->hasBcc('template-bcc@example.com');
    });
});

test('handles string email addresses', function (): void {
    Mail::fake();

    $tenant = Tenant::factory()->create();

    $action = SendMail::make([
        'tenant_id' => $tenant->id,
        'to' => 'single@example.com',
        'cc' => 'cc@example.com',
        'bcc' => 'bcc@example.com',
        'subject' => 'Test Subject',
        'text_body' => 'Test Text Body',
        'html_body' => '<p>Test Body</p>',
        'queue' => false,
    ]);

    $result = $action->validate()->execute();

    expect($result['success'])->toBeTrue();

    Mail::assertSent(GenericMail::class, function ($mail) {
        return $mail->hasTo('single@example.com')
            && $mail->hasCc('cc@example.com')
            && $mail->hasBcc('bcc@example.com');
    });
});

test('provided data overrides template', function (): void {
    Mail::fake();

    $template = EmailTemplate::factory()->create([
        'subject' => 'Template Subject',
        'html_body' => '<p>Template Body</p>',
    ]);

    $action = SendMail::make([
        'to' => ['override@example.com'],
        'subject' => 'Override Subject',
        'html_body' => '<p>Override Body</p>',
        'template_id' => $template->id,
    ]);

    $result = $action->validate()->execute();

    expect($result['success'])->toBeTrue();

    Mail::assertSent(GenericMail::class, function ($mail) {
        return $mail->hasTo('override@example.com');
    });
});

test('send mail queued', function (): void {
    Mail::fake();

    $tenant = Tenant::factory()->create();

    $action = SendMail::make([
        'tenant_id' => $tenant->id,
        'to' => ['test@example.com'],
        'subject' => 'Queued Test',
        'text_body' => 'Queued Test Text Body',
        'html_body' => '<p>Queued Test Body</p>',
        'blade_parameters_serialized' => false,
        'queue' => true,
    ]);

    $result = $action->validate()->execute();

    expect($result['success'])->toBeTrue();

    Mail::assertQueued(GenericMail::class);
});

test('send mail with all validated keys', function (): void {
    Mail::fake();

    $tenant = Tenant::factory()->create();
    $template = EmailTemplate::factory()->create([
        'subject' => 'Template Subject',
        'html_body' => '<p>Template HTML Body</p>',
        'text_body' => 'Template Text Body',
    ]);

    $action = SendMail::make([
        'tenant_id' => $tenant->id,
        'template_id' => $template->id,
        'to' => ['test@example.com', 'test2@example.com'],
        'cc' => ['cc1@example.com', 'cc2@example.com'],
        'bcc' => ['bcc1@example.com', 'bcc2@example.com'],
        'subject' => 'Override Subject',
        'text_body' => 'Override Text Body',
        'html_body' => '<p>Override HTML Body</p>',
        'attachments' => [
            ['path' => '/tmp/test1.pdf', 'name' => 'test1.pdf'],
            ['path' => '/tmp/test2.pdf', 'name' => 'test2.pdf'],
        ],
        'blade_parameters' => ['name' => 'John Doe', 'company' => 'Test Company'],
        'blade_parameters_serialized' => false,
        'queue' => true,
    ]);

    $result = $action->validate()->execute();

    expect($result['success'])->toBeTrue();
    expect($result['message'])->toEqual(__('Email(s) sent successfully!'));

    Mail::assertQueued(GenericMail::class, function ($mail) {
        return $mail->hasTo('test@example.com')
            && $mail->hasTo('test2@example.com')
            && $mail->hasCc('cc1@example.com')
            && $mail->hasCc('cc2@example.com')
            && $mail->hasBcc('bcc1@example.com')
            && $mail->hasBcc('bcc2@example.com');
    });
});

test('send mail with attachments', function (): void {
    Mail::fake();

    $tenant = Tenant::factory()->create();

    $action = SendMail::make([
        'tenant_id' => $tenant->id,
        'to' => ['test@example.com'],
        'cc' => ['cc@example.com'],
        'bcc' => ['bcc@example.com'],
        'subject' => 'Mail with Attachments',
        'text_body' => 'See attached files',
        'html_body' => '<p>See attached files</p>',
        'attachments' => [
            ['path' => '/tmp/test.pdf', 'name' => 'test.pdf'],
            ['id' => 123],
        ],
        'blade_parameters_serialized' => false,
        'queue' => false,
    ]);

    $result = $action->validate()->execute();

    expect($result['success'])->toBeTrue();

    Mail::assertSent(GenericMail::class);
});

test('send mail with blade parameters', function (): void {
    Mail::fake();

    $tenant = Tenant::factory()->create();
    $template = EmailTemplate::factory()->create([
        'subject' => 'Hello {{ $name }}',
        'html_body' => '<p>Hello {{ $name }}, welcome!</p>',
        'text_body' => 'Hello {{ $name }}, welcome!',
    ]);

    $action = SendMail::make([
        'tenant_id' => $tenant->id,
        'to' => ['test@example.com'],
        'cc' => ['cc@example.com'],
        'bcc' => ['bcc@example.com'],
        'template_id' => $template->id,
        'blade_parameters' => [
            'name' => 'John Doe',
        ],
        'blade_parameters_serialized' => false,
        'queue' => false,
    ]);

    $result = $action->validate()->execute();

    expect($result['success'])->toBeTrue();

    Mail::assertSent(GenericMail::class);
});

test('send mail with cc and bcc', function (): void {
    Mail::fake();

    $tenant = Tenant::factory()->create();

    $action = SendMail::make([
        'tenant_id' => $tenant->id,
        'to' => ['test@example.com'],
        'cc' => ['cc@example.com'],
        'bcc' => ['bcc@example.com'],
        'subject' => 'Test Subject',
        'text_body' => 'Test Text Body',
        'html_body' => '<p>Test HTML Body</p>',
        'attachments' => [],
        'blade_parameters' => [],
        'blade_parameters_serialized' => false,
        'queue' => false,
    ]);

    $result = $action->validate()->execute();

    expect($result['success'])->toBeTrue();

    Mail::assertSent(GenericMail::class, function ($mail) {
        return $mail->hasTo('test@example.com')
            && $mail->hasCc('cc@example.com')
            && $mail->hasBcc('bcc@example.com');
    });
});

test('send mail with template', function (): void {
    Mail::fake();

    $template = EmailTemplate::factory()->create([
        'subject' => 'Template Subject',
        'html_body' => '<p>Template HTML Body</p>',
        'text_body' => 'Template Text Body',
        'cc' => ['template-cc@example.com'],
    ]);

    $action = SendMail::make([
        'to' => ['test@example.com'],
        'template_id' => $template->id,
    ]);

    $result = $action->validate()->execute();

    expect($result['success'])->toBeTrue();

    Mail::assertSent(GenericMail::class, function ($mail) {
        return $mail->hasTo('test@example.com')
            && $mail->hasCc('template-cc@example.com');
    });
});

test('send simple mail', function (): void {
    Mail::fake();

    $tenant = Tenant::factory()->create();

    $action = SendMail::make([
        'tenant_id' => $tenant->id,
        'to' => ['test@example.com'],
        'cc' => [],
        'bcc' => [],
        'subject' => 'Test Subject',
        'text_body' => 'Test Text Body',
        'html_body' => '<p>Test HTML Body</p>',
        'attachments' => [],
        'blade_parameters' => [],
        'blade_parameters_serialized' => false,
        'queue' => false,
    ]);

    $result = $action->validate()->execute();

    expect($result['success'])->toBeTrue();
    expect($result['message'])->toEqual(__('Email(s) sent successfully!'));

    Mail::assertSent(GenericMail::class, function ($mail) {
        return $mail->hasTo('test@example.com');
    });
});

test('template overrides empty fields', function (): void {
    Mail::fake();

    $template = EmailTemplate::factory()->create([
        'subject' => 'Default Subject',
        'html_body' => '<p>Default Body</p>',
        'to' => ['default@example.com'],
    ]);

    $action = SendMail::make([
        'template_id' => $template->id,
    ]);

    $result = $action->validate()->execute();

    expect($result['success'])->toBeTrue();

    Mail::assertSent(GenericMail::class, function ($mail) {
        return $mail->hasTo('default@example.com');
    });
});

test('send mail with custom mail account', function (): void {
    $fakeMail = Mail::fake();

    $tenant = Tenant::factory()->create();
    $mailAccount = MailAccount::factory()->create([
        'smtp_email' => 'custom@example.com',
        'smtp_password' => 'password123',
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => 587,
        'smtp_encryption' => 'tls',
        'smtp_mailer' => 'smtp',
    ]);

    Mail::shouldReceive('mailer')
        ->once()
        ->andReturn($fakeMail);

    Mail::shouldReceive('build')
        ->once()
        ->with(Mockery::on(function ($config) use ($mailAccount) {
            return $config['username'] === $mailAccount->smtp_email
                && $config['host'] === $mailAccount->smtp_host
                && $config['port'] === $mailAccount->smtp_port
                && $config['encryption'] === $mailAccount->smtp_encryption;
        }))
        ->andReturn($fakeMail);

    $action = SendMail::make([
        'tenant_id' => $tenant->id,
        'mail_account_id' => $mailAccount->id,
        'to' => ['test@example.com'],
        'subject' => 'Custom Mail Test',
        'html_body' => '<p>Test Body</p>',
        'queue' => false,
    ]);

    $result = $action->validate()->execute();

    expect($result['success'])->toBeTrue();

    $fakeMail->assertSent(GenericMail::class, function ($mail) use ($mailAccount) {
        return $mail->hasTo('test@example.com')
            && $mail->from[0]['address'] === $mailAccount->smtp_email;
    });
});

test('send mail with custom mail account and queue sends immediately', function (): void {
    $fakeMail = Mail::fake();

    $tenant = Tenant::factory()->create();
    $mailAccount = MailAccount::factory()->create([
        'smtp_email' => 'queued@example.com',
        'smtp_password' => 'password456',
        'smtp_host' => 'smtp.queue.com',
        'smtp_port' => 465,
        'smtp_encryption' => 'ssl',
        'smtp_mailer' => 'smtp',
    ]);

    Mail::shouldReceive('mailer')
        ->once()
        ->andReturn($fakeMail);

    Mail::shouldReceive('build')
        ->once()
        ->with(Mockery::on(function ($config) use ($mailAccount) {
            return $config['username'] === $mailAccount->smtp_email
                && $config['host'] === $mailAccount->smtp_host
                && $config['port'] === $mailAccount->smtp_port
                && $config['encryption'] === $mailAccount->smtp_encryption;
        }))
        ->andReturn($fakeMail);

    $action = SendMail::make([
        'tenant_id' => $tenant->id,
        'mail_account_id' => $mailAccount->id,
        'to' => ['test@example.com'],
        'cc' => ['cc@example.com'],
        'bcc' => ['bcc@example.com'],
        'subject' => 'Queued Custom Mail',
        'html_body' => '<p>Queued Test</p>',
        'queue' => true,
    ]);

    $result = $action->validate()->execute();

    expect($result['success'])->toBeTrue();

    // When mail_account_id is provided, mail should be sent immediately despite queue = true
    $fakeMail->assertSent(GenericMail::class, function ($mail) use ($mailAccount) {
        return $mail->hasTo('test@example.com')
            && $mail->hasCc('cc@example.com')
            && $mail->hasBcc('bcc@example.com')
            && $mail->from[0]['address'] === $mailAccount->smtp_email;
    });

    // Should NOT be queued
    $fakeMail->assertNotQueued(GenericMail::class);
});

test('send mail with template in different language', function (): void {
    Mail::fake();

    $defaultLanguage = FluxErp\Models\Language::factory()->create(['is_default' => true]);
    $germanLanguage = FluxErp\Models\Language::factory()->create([
        'name' => 'German',
        'iso_name' => 'de',
        'language_code' => 'de_DE',
        'is_default' => false,
    ]);

    $template = EmailTemplate::factory()->create([
        'subject' => 'English Subject',
        'html_body' => '<p>English Body</p>',
        'text_body' => 'English Text Body',
    ]);

    // Create German translation
    FluxErp\Actions\AttributeTranslation\UpsertAttributeTranslation::make([
        'language_id' => $germanLanguage->id,
        'model_type' => morph_alias(EmailTemplate::class),
        'model_id' => $template->id,
        'attribute' => 'subject',
        'value' => 'Deutscher Betreff',
    ])->validate()->execute();

    FluxErp\Actions\AttributeTranslation\UpsertAttributeTranslation::make([
        'language_id' => $germanLanguage->id,
        'model_type' => morph_alias(EmailTemplate::class),
        'model_id' => $template->id,
        'attribute' => 'html_body',
        'value' => '<p>Deutscher Inhalt</p>',
    ])->validate()->execute();

    $action = SendMail::make([
        'to' => ['german-customer@example.com'],
        'template_id' => $template->id,
        'language_id' => $germanLanguage->id,
    ]);

    $result = $action->validate()->execute();

    expect($result['success'])->toBeTrue();

    Mail::assertSent(GenericMail::class, function ($mail) {
        return $mail->hasTo('german-customer@example.com');
    });
});

test('send mail with template falls back to default language when translation missing', function (): void {
    Mail::fake();

    $defaultLanguage = FluxErp\Models\Language::factory()->create(['is_default' => true]);
    $frenchLanguage = FluxErp\Models\Language::factory()->create([
        'name' => 'French',
        'iso_name' => 'fr',
        'language_code' => 'fr_FR',
        'is_default' => false,
    ]);

    $template = EmailTemplate::factory()->create([
        'subject' => 'Default English Subject',
        'html_body' => '<p>Default English Body</p>',
        'text_body' => 'Default English Text',
    ]);

    // No French translation exists - should fall back to default

    $action = SendMail::make([
        'to' => ['french-customer@example.com'],
        'template_id' => $template->id,
        'language_id' => $frenchLanguage->id,
    ]);

    $result = $action->validate()->execute();

    expect($result['success'])->toBeTrue();

    Mail::assertSent(GenericMail::class, function ($mail) {
        return $mail->hasTo('french-customer@example.com');
    });
});

test('batch emails with different languages use correct translations', function (): void {
    Mail::fake();

    $defaultLanguage = FluxErp\Models\Language::factory()->create(['is_default' => true]);
    $germanLanguage = FluxErp\Models\Language::factory()->create([
        'name' => 'German',
        'iso_name' => 'de',
        'language_code' => 'de_DE',
        'is_default' => false,
    ]);
    $spanishLanguage = FluxErp\Models\Language::factory()->create([
        'name' => 'Spanish',
        'iso_name' => 'es',
        'language_code' => 'es_ES',
        'is_default' => false,
    ]);

    $template = EmailTemplate::factory()->create([
        'subject' => 'English Welcome',
        'html_body' => '<p>Welcome!</p>',
    ]);

    // German translation
    FluxErp\Actions\AttributeTranslation\UpsertAttributeTranslation::make([
        'language_id' => $germanLanguage->id,
        'model_type' => morph_alias(EmailTemplate::class),
        'model_id' => $template->id,
        'attribute' => 'subject',
        'value' => 'Willkommen',
    ])->validate()->execute();

    // Spanish translation
    FluxErp\Actions\AttributeTranslation\UpsertAttributeTranslation::make([
        'language_id' => $spanishLanguage->id,
        'model_type' => morph_alias(EmailTemplate::class),
        'model_id' => $template->id,
        'attribute' => 'subject',
        'value' => 'Bienvenido',
    ])->validate()->execute();

    // Send to German customer
    $germanResult = SendMail::make([
        'to' => ['german@example.com'],
        'template_id' => $template->id,
        'language_id' => $germanLanguage->id,
    ])->validate()->execute();

    expect($germanResult['success'])->toBeTrue();

    // Send to Spanish customer
    $spanishResult = SendMail::make([
        'to' => ['spanish@example.com'],
        'template_id' => $template->id,
        'language_id' => $spanishLanguage->id,
    ])->validate()->execute();

    expect($spanishResult['success'])->toBeTrue();

    // Send to English customer (default/no language_id)
    $englishResult = SendMail::make([
        'to' => ['english@example.com'],
        'template_id' => $template->id,
    ])->validate()->execute();

    expect($englishResult['success'])->toBeTrue();

    Mail::assertSent(GenericMail::class, 3);
    Mail::assertSent(GenericMail::class, fn ($mail) => $mail->hasTo('german@example.com'));
    Mail::assertSent(GenericMail::class, fn ($mail) => $mail->hasTo('spanish@example.com'));
    Mail::assertSent(GenericMail::class, fn ($mail) => $mail->hasTo('english@example.com'));
});
