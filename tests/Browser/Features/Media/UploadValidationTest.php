<?php

beforeEach(function (): void {
    $this->defaultLanguage->update(['language_code' => 'de']);
});

test('validateFiles names the file, its size and the limit', function (): void {
    $result = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->script(<<<'JS'
            () => {
                const file = new File(['x'.repeat(2048)], 'vertrag.pdf', {
                    type: 'application/pdf',
                });
                const rejected = window.$nuxbe.validateFiles([file], { maxSize: 1024 });

                return [rejected.reason, rejected.file.name, rejected.size, rejected.maxSize].join('|');
            }
        JS);

    expect($result)->toBe('size|vertrag.pdf|2,0 KB|1,0 KB');
});

test('validateFiles rejects a type the input does not accept', function (): void {
    $result = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->script(<<<'JS'
            () => {
                const file = new File(['x'], 'schadcode.exe', {
                    type: 'application/x-msdownload',
                });
                const rejected = window.$nuxbe.validateFiles([file], {
                    maxSize: 1048576,
                    accept: 'application/pdf, image/*',
                });

                return [rejected.reason, rejected.file.name].join('|');
            }
        JS);

    expect($result)->toBe('type|schadcode.exe');
});

test('validateFiles passes a file that fits', function (): void {
    $result = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->script(<<<'JS'
            () => {
                const file = new File(['x'], 'beleg.pdf', { type: 'application/pdf' });

                return window.$nuxbe.validateFiles([file], {
                    maxSize: 1048576,
                    accept: 'application/pdf',
                }) === null;
            }
        JS);

    expect($result)->toBeTrue();
});

test('an accept list separated by semicolons is understood', function (): void {
    $result = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->script(<<<'JS'
            () => {
                const file = new File(['x'], 'rechnung.pdf', { type: 'application/pdf' });

                return window.$nuxbe.validateFiles([file], {
                    maxSize: 1048576,
                    accept: 'application/pdf;image/*',
                }) === null;
            }
        JS);

    expect($result)->toBeTrue();
});

test('a file without a mime type passes a mime only accept list', function (): void {
    $result = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->script(<<<'JS'
            () => {
                const file = new File(['x'], 'logo.svg', { type: '' });

                return window.$nuxbe.validateFiles([file], {
                    maxSize: 1048576,
                    accept: 'image/jpeg, image/png, image/svg+xml',
                }) === null;
            }
        JS);

    expect($result)->toBeTrue();
});

test('a file name cannot inject markup into the message', function (): void {
    $result = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->script(<<<'JS'
            () => window.$nuxbe.escapeHtml('<img src=x onerror=alert(1)>.pdf')
        JS);

    expect($result)->toBe('&lt;img src=x onerror=alert(1)&gt;.pdf');
});
