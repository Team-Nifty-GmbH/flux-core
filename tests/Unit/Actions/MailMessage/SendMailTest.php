<?php

namespace Tests\Unit\Actions\MailMessage;

use Exception;
use FluxErp\Actions\MailMessage\SendMail;
use FluxErp\Mail\GenericMail;
use FluxErp\Models\EmailTemplate;
use FluxErp\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

class SendMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_mail_failure(): void
    {
        Mail::shouldReceive('to')->andReturnSelf();
        Mail::shouldReceive('cc')->andReturnSelf();
        Mail::shouldReceive('bcc')->andReturnSelf();
        Mail::shouldReceive('send')->andThrow(new Exception('Mail server error'));

        $action = SendMail::make([
            'to' => ['test@example.com'],
            'subject' => 'Test Subject',
            'html_body' => '<p>Test Body</p>',
        ]);

        $result = $action->validate()->execute();

        $this->assertFalse($result['success']);
        $this->assertEquals(__('Failed to send email!'), $result['message']);
        $this->assertEquals('Mail server error', $result['error']);
    }

    public function test_handles_null_values_correctly(): void
    {
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

        $this->assertTrue($result['success']);

        Mail::assertSent(GenericMail::class, function ($mail) {
            return $mail->hasTo('user@example.com')
                && $mail->hasCc('template-cc@example.com')
                && $mail->hasBcc('template-bcc@example.com');
        });
    }

    public function test_handles_string_email_addresses(): void
    {
        Mail::fake();

        $action = SendMail::make([
            'to' => 'single@example.com',
            'cc' => 'cc@example.com',
            'bcc' => 'bcc@example.com',
            'subject' => 'Test Subject',
            'html_body' => '<p>Test Body</p>',
        ]);

        $result = $action->validate()->execute();

        $this->assertTrue($result['success']);

        Mail::assertSent(GenericMail::class, function ($mail) {
            return $mail->hasTo('single@example.com')
                && $mail->hasCc('cc@example.com')
                && $mail->hasBcc('bcc@example.com');
        });
    }

    public function test_provided_data_overrides_template(): void
    {
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

        $this->assertTrue($result['success']);

        Mail::assertSent(GenericMail::class, function ($mail) {
            return $mail->hasTo('override@example.com');
        });
    }

    public function test_send_mail_queued(): void
    {
        Mail::fake();

        $action = SendMail::make([
            'to' => ['test@example.com'],
            'subject' => 'Queued Test',
            'html_body' => '<p>Queued Test Body</p>',
            'queue' => true,
        ]);

        $result = $action->validate()->execute();

        $this->assertTrue($result['success']);

        Mail::assertQueued(GenericMail::class);
    }

    public function test_send_mail_with_attachments(): void
    {
        Mail::fake();

        $action = SendMail::make([
            'to' => ['test@example.com'],
            'subject' => 'Mail with Attachments',
            'html_body' => '<p>See attached files</p>',
            'attachments' => [
                ['path' => '/tmp/test.pdf', 'name' => 'test.pdf'],
                ['id' => 123],
            ],
        ]);

        $result = $action->validate()->execute();

        $this->assertTrue($result['success']);

        Mail::assertSent(GenericMail::class);
    }

    public function test_send_mail_with_blade_parameters(): void
    {
        Mail::fake();

        $template = EmailTemplate::factory()->create([
            'subject' => 'Hello {{ $name }}',
            'html_body' => '<p>Hello {{ $name }}, welcome!</p>',
            'text_body' => 'Hello {{ $name }}, welcome!',
        ]);

        $action = SendMail::make([
            'to' => ['test@example.com'],
            'template_id' => $template->id,
            'blade_parameters' => [
                'name' => 'John Doe',
            ],
        ]);

        $result = $action->validate()->execute();

        $this->assertTrue($result['success']);

        Mail::assertSent(GenericMail::class);
    }

    public function test_send_mail_with_cc_and_bcc(): void
    {
        Mail::fake();

        $action = SendMail::make([
            'to' => ['test@example.com'],
            'cc' => ['cc@example.com'],
            'bcc' => ['bcc@example.com'],
            'subject' => 'Test Subject',
            'html_body' => '<p>Test HTML Body</p>',
        ]);

        $result = $action->validate()->execute();

        $this->assertTrue($result['success']);

        Mail::assertSent(GenericMail::class, function ($mail) {
            return $mail->hasTo('test@example.com')
                && $mail->hasCc('cc@example.com')
                && $mail->hasBcc('bcc@example.com');
        });
    }

    public function test_send_mail_with_template(): void
    {
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

        $this->assertTrue($result['success']);

        Mail::assertSent(GenericMail::class, function ($mail) {
            return $mail->hasTo('test@example.com')
                && $mail->hasCc('template-cc@example.com');
        });
    }

    public function test_send_simple_mail(): void
    {
        Mail::fake();

        $action = SendMail::make([
            'to' => ['test@example.com'],
            'subject' => 'Test Subject',
            'html_body' => '<p>Test HTML Body</p>',
        ]);

        $result = $action->validate()->execute();

        $this->assertTrue($result['success']);
        $this->assertEquals(__('Email(s) sent successfully!'), $result['message']);

        Mail::assertSent(GenericMail::class, function ($mail) {
            return $mail->hasTo('test@example.com');
        });
    }

    public function test_template_overrides_empty_fields(): void
    {
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

        $this->assertTrue($result['success']);

        Mail::assertSent(GenericMail::class, function ($mail) {
            return $mail->hasTo('default@example.com');
        });
    }
}
