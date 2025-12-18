<?php

use FluxErp\Livewire\Settings\EmailTemplates;
use FluxErp\Models\EmailTemplate;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->emailTemplate = EmailTemplate::factory()->create();
});

test('renders successfully', function (): void {
    Livewire::test(EmailTemplates::class)
        ->assertOk();
});

test('can create email template', function (): void {
    Livewire::test(EmailTemplates::class)
        ->assertOk()
        ->assertHasNoErrors()
        ->set('emailTemplateForm.name', $name = Str::uuid()->toString())
        ->set('emailTemplateForm.subject', 'Test Subject')
        ->set('emailTemplateForm.html_body', '<p>Test Body</p>')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseHas('email_templates', [
        'name' => $name,
        'subject' => 'Test Subject',
    ]);
});

test('can update email template', function (): void {
    Livewire::test(EmailTemplates::class)
        ->assertOk()
        ->call('edit', $this->emailTemplate->id)
        ->assertSet('emailTemplateForm.id', $this->emailTemplate->id)
        ->set('emailTemplateForm.name', 'Updated Name')
        ->set('emailTemplateForm.subject', 'Updated Subject')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    $refreshed = $this->emailTemplate->refresh();
    expect($refreshed->name)->toEqual('Updated Name');
    expect($refreshed->subject)->toEqual('Updated Subject');
});

test('can delete email template', function (): void {
    $emailTemplateId = $this->emailTemplate->id;

    Livewire::test(EmailTemplates::class)
        ->assertOk()
        ->call('edit', $emailTemplateId)
        ->call('delete', $emailTemplateId)
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('email_templates', [
        'id' => $emailTemplateId,
    ]);
});

test('can set model type', function (): void {
    Livewire::test(EmailTemplates::class)
        ->assertOk()
        ->set('emailTemplateForm.name', Str::uuid()->toString())
        ->set('emailTemplateForm.model_type', 'order')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();
});

test('model types only contains offers printing models', function (): void {
    $component = Livewire::test(EmailTemplates::class);
    $viewData = $component->viewData('modelTypes');

    expect($viewData)->toBeArray();
    expect($viewData[0]['value'])->toBeNull();
    expect($viewData[0]['label'])->toEqual(__('General'));
});

test('validation fails with invalid email in to field', function (): void {
    Livewire::test(EmailTemplates::class)
        ->assertOk()
        ->set('emailTemplateForm.name', Str::uuid()->toString())
        ->set('emailTemplateForm.to', ['invalid-email'])
        ->call('save')
        ->assertHasErrors();
});

test('can edit existing template and load form data', function (): void {
    Livewire::test(EmailTemplates::class)
        ->assertOk()
        ->call('edit', $this->emailTemplate->id)
        ->assertSet('emailTemplateForm.id', $this->emailTemplate->id)
        ->assertSet('emailTemplateForm.name', $this->emailTemplate->name)
        ->assertSet('emailTemplateForm.subject', $this->emailTemplate->subject);
});
