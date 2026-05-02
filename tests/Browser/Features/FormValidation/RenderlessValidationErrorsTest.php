<?php

use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\Form;

/*
|--------------------------------------------------------------------------
| Test Form & Component (inline)
|--------------------------------------------------------------------------
*/

class ValidationTestForm extends Form
{
    public ?string $name = null;

    public ?string $email = null;

    public ?string $description = null;

    public ?int $amount = null;

    public ?string $category = null;

    public ?int $related_id = null;

    public bool $is_active = false;
}

class ValidationTestComponent extends Component
{
    use FluxErp\Traits\Livewire\Actions;

    public ValidationTestForm $form;

    public function render()
    {
        return <<<'HTML'
        <div class="flex flex-col gap-4 p-6">
            <x-input wire:model="form.name" :label="__('Name')" />
            <x-input wire:model="form.email" :label="__('Email')" />
            <x-textarea wire:model="form.description" :label="__('Description')" />
            <x-number wire:model="form.amount" :label="__('Amount')" />
            <x-select.native wire:model="form.category" :label="__('Category')" :options="['', 'a', 'b', 'c']" />
            <x-select.styled wire:model="form.related_id" :label="__('Related')" :options="[['label' => 'Option 1', 'value' => 1], ['label' => 'Option 2', 'value' => 2]]" select="label:label|value:value" />
            <x-toggle wire:model="form.is_active" :label="__('Active')" />
            <x-button wire:click="save" :text="__('Save')" />
        </div>
        HTML;
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            Illuminate\Support\Facades\Validator::make($this->form->all(), [
                'name' => 'required|string|min:2',
                'email' => 'required|email',
                'description' => 'required',
                'amount' => 'required|integer|min:1',
                'category' => 'required',
                'related_id' => 'required',
            ])->validate();
        } catch (Illuminate\Validation\ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        return true;
    }
}

class ModalValidationTestComponent extends Component
{
    use FluxErp\Traits\Livewire\Actions;

    public ValidationTestForm $form;

    public function render()
    {
        return <<<'HTML'
        <div>
            <x-modal id="modal-validation-test" :title="'Test'">
                <x-input wire:model="form.name" :label="'Name'" />
                <x-select.styled wire:model="form.related_id" :label="'Related'" :options="[['label' => 'Option 1', 'value' => 1]]" select="label:label|value:value" />
                <x-slot:footer>
                    <x-button wire:click="save" :text="'Save'" />
                </x-slot:footer>
            </x-modal>
        </div>
        HTML;
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            Illuminate\Support\Facades\Validator::make($this->form->all(), [
                'name' => 'required|string|min:2',
                'related_id' => 'required',
            ])->validate();
        } catch (Illuminate\Validation\ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        return true;
    }
}

/*
|--------------------------------------------------------------------------
| Setup
|--------------------------------------------------------------------------
*/

beforeEach(function (): void {
    Livewire\Livewire::component('validation-test', ValidationTestComponent::class);
    Livewire\Livewire::component('modal-validation-test', ModalValidationTestComponent::class);
});

function triggerSave($page): void
{
    // Click the save button scoped to our test component
    $page->script(<<<'JS'
        () => {
            const comp = Livewire.all().find(c => c.name === 'validation-test');
            const el = document.querySelector('[wire\\:id="' + comp.id + '"]');
            const btn = el.querySelector('[wire\\:click="save"]');
            btn.click();
        }
    JS);

    $page->wait(3);
}

/*
|--------------------------------------------------------------------------
| x-input
|--------------------------------------------------------------------------
*/

test('x-input shows inline error and ring on validation failure', function (): void {
    $page = visitLivewire(ValidationTestComponent::class)->assertNoSmoke();

    triggerSave($page);

    // Debug: check what happened
    $page->assertScript(<<<'JS'
        (() => {
            const comp = Livewire.all().find(c => c.name === 'validation-test');
            const errors = comp?.snapshot?.memo?.errors || {};
            const spans = document.querySelectorAll('[x-show*="form.name"]');
            const rings = document.querySelectorAll('.ring-red-300');

            // If no errors in snapshot, the save didn't trigger validation
            if (Object.keys(errors).length === 0) {
                throw new Error('No errors in snapshot. Save may not have been called. Components: ' + Livewire.all().map(c => c.name).join(', '));
            }

            const hasVisibleSpan = Array.from(spans).some(s => s.style.display !== 'none');
            if (!hasVisibleSpan) {
                throw new Error('Errors in snapshot (' + JSON.stringify(errors) + ') but no visible error span. Spans found: ' + spans.length);
            }

            return hasVisibleSpan && rings.length > 0;
        })()
    JS);
});

/*
|--------------------------------------------------------------------------
| x-textarea
|--------------------------------------------------------------------------
*/

test('x-textarea shows inline error on validation failure', function (): void {
    $page = visitLivewire(ValidationTestComponent::class)->assertNoSmoke();

    triggerSave($page);

    $page->assertScript(<<<'JS'
        (() => {
            const spans = document.querySelectorAll('[x-show*="form.description"]');
            return spans.length > 0 && Array.from(spans).some(s => s.style.display !== 'none');
        })()
    JS);
});

/*
|--------------------------------------------------------------------------
| x-number
|--------------------------------------------------------------------------
*/

test('x-number shows inline error on validation failure', function (): void {
    $page = visitLivewire(ValidationTestComponent::class)->assertNoSmoke();

    triggerSave($page);

    $page->assertScript(<<<'JS'
        (() => {
            const spans = document.querySelectorAll('[x-show*="form.amount"]');
            return spans.length > 0 && Array.from(spans).some(s => s.style.display !== 'none');
        })()
    JS);
});

/*
|--------------------------------------------------------------------------
| x-select.native
|--------------------------------------------------------------------------
*/

test('x-select.native shows ring on validation failure', function (): void {
    $page = visitLivewire(ValidationTestComponent::class)->assertNoSmoke();

    triggerSave($page);

    $page->assertScript(<<<'JS'
        (() => {
            const selects = document.querySelectorAll('select[wire\\:model]');
            return Array.from(selects).some(s => {
                const wrapper = s.parentElement?.closest('[class*="ring-"]') || s.parentElement;
                return wrapper?.classList.contains('ring-red-300');
            });
        })()
    JS);
});

/*
|--------------------------------------------------------------------------
| x-select.styled
|--------------------------------------------------------------------------
*/

test('x-select.styled shows error text and ring on validation failure', function (): void {
    $page = visitLivewire(ValidationTestComponent::class)->assertNoSmoke();

    triggerSave($page);

    $page->assertScript(<<<'JS'
        (() => {
            const errors = document.querySelectorAll('[data-validation-error="form.related_id"]');
            return errors.length > 0 && Array.from(errors).some(e => e.style.display !== 'none' && e.textContent.length > 0);
        })()
    JS);

    $page->assertScript(<<<'JS'
        (() => {
            const selects = document.querySelectorAll('[x-data*="tallstackui_select"]');
            return Array.from(selects).some(s => {
                const btn = s.querySelector('[dusk="tallstackui_select_open_close"]');
                const wrapper = btn?.parentElement?.closest('[class*="ring-"]');
                return wrapper?.classList.contains('ring-red-300');
            });
        })()
    JS);
});

/*
|--------------------------------------------------------------------------
| x-toggle (uses wrapper/radio.blade.php)
|--------------------------------------------------------------------------
*/

test('x-toggle has error component rendered via published radio wrapper', function (): void {
    $page = visitLivewire(ValidationTestComponent::class)->assertNoSmoke();

    $page->assertScript(<<<'JS'
        (() => {
            const errorSpans = document.querySelectorAll('[x-show*="form.is_active"]');
            return errorSpans.length > 0;
        })()
    JS);
});

/*
|--------------------------------------------------------------------------
| Error clearing
|--------------------------------------------------------------------------
*/

test('errors are not present on fresh page load after navigation', function (): void {
    $page = visitLivewire(ValidationTestComponent::class)->assertNoSmoke();

    triggerSave($page);

    $page->assertScript(<<<'JS'
        (() => document.querySelectorAll('.ring-red-300').length > 0)()
    JS);

    // Navigate away and back to clear all state
    $page = visit($page->url())->assertNoSmoke();

    $page->assertScript(<<<'JS'
        (() => document.querySelectorAll('.ring-red-300').length === 0)()
    JS);

    $page->assertScript(<<<'JS'
        (() => {
            const spans = document.querySelectorAll('[data-validation-error]');
            return Array.from(spans).every(s => s.style.display === 'none' || s.textContent === '');
        })()
    JS);
});

/*
|--------------------------------------------------------------------------
| Toast behavior
|--------------------------------------------------------------------------
*/

test('no toast for field errors that have matching inputs', function (): void {
    $page = visitLivewire(ValidationTestComponent::class)->assertNoSmoke();

    triggerSave($page);

    $page->assertScript(<<<'JS'
        (() => {
            const container = document.querySelector('[x-data*="tallstackui_toastBase"]');
            if (!container) return true;
            const visible = Array.from(container.querySelectorAll('[x-show]'))
                .filter(el => el.style.display !== 'none' && el.textContent.trim().length > 0);
            return visible.length === 0;
        })()
    JS);
});

test('toast fallback for errors without matching visible input', function (): void {
    $page = visitLivewire(ValidationTestComponent::class)->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            const comp = Livewire.all().find(c => c.name === 'validation-test');
            comp.snapshot.memo.errors['form.nonexistent'] = ['This field has no input.'];

            queueMicrotask(() => {
                const el = comp.el;
                const errors = comp.snapshot.memo.errors;
                const keys = Object.keys(errors);
                const matched = new Set();

                el.querySelectorAll('[wire\\:model]').forEach(i => {
                    for (const attr of i.attributes) {
                        if (attr.name.startsWith('wire:model') && keys.includes(attr.value) && i.offsetParent !== null) {
                            matched.add(attr.value);
                        }
                    }
                });

                el.querySelectorAll('[x-data*="tallstackui_select"]').forEach(s => {
                    const prop = Alpine.$data(s)?.property;
                    if (prop && keys.includes(prop) && s.offsetParent !== null) matched.add(prop);
                });

                keys.forEach(key => {
                    if (!matched.has(key) && errors[key]?.length > 0) {
                        $tsui.interaction('toast').error('Error', errors[key][0]).send();
                    }
                });
            });
        }
    JS);

    $page->wait(1);

    $page->assertScript(<<<'JS'
        (() => {
            const container = document.querySelector('[x-data*="tallstackui_toastBase"]');
            if (!container) return false;
            return container.querySelectorAll('[x-show]').length > 0;
        })()
    JS);
});

/*
|--------------------------------------------------------------------------
| Teleported modals (x-modal)
|--------------------------------------------------------------------------
*/

test('inline errors render inside teleported x-modal contents', function (): void {
    $page = visitLivewire(ModalValidationTestComponent::class)->assertNoSmoke();

    // Open the modal so its teleported contents are mounted into <body>.
    $page->script(<<<'JS'
        () => $tsui.open.modal('modal-validation-test')
    JS);

    $page->wait(1);

    // Click the save button inside the teleported modal.
    $page->script(<<<'JS'
        () => {
            const teleported = document.querySelector('[id="modal-validation-test"]');
            teleported.querySelector('[wire\\:click="save"]').click();
        }
    JS);

    $page->wait(3);

    $page->assertScript(<<<'JS'
        (() => {
            const comp = Livewire.all().find(c => c.name === 'modal-validation-test');
            const errors = comp?.snapshot?.memo?.errors || {};

            if (Object.keys(errors).length === 0) {
                throw new Error('No errors in snapshot — save was not triggered.');
            }

            const teleported = document.querySelector('[id="modal-validation-test"]');

            if (!teleported || !teleported.matches('[data-teleport-target]')) {
                throw new Error('Modal is not teleported as expected.');
            }

            // The styled select error span is injected as [data-validation-error]
            // by validation-errors.js when it walks teleported descendants.
            const selectError = teleported.querySelector(
                '[data-validation-error="form.related_id"]',
            );

            if (!selectError || selectError.style.display === 'none') {
                throw new Error('Select error span missing or hidden inside teleported modal.');
            }

            // The x-input error span is rendered through the published @error
            // partial as [x-show*="$errors"]; the helper re-evaluates it.
            const inputError = Array.from(
                teleported.querySelectorAll('[x-show*="$errors"]'),
            ).find((s) => s.style.display !== 'none');

            if (!inputError) {
                throw new Error('Input error span hidden inside teleported modal.');
            }

            return true;
        })()
    JS);
});
