<?php

use FluxErp\Mail\GenericMail;
use FluxErp\Models\Tenant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
    $this->tenant = Tenant::factory()->create();
});

test('the logo carries its size as attributes for outlook', function (): void {
    $this->tenant
        ->addMedia(UploadedFile::fake()->image('logo.png', 1319, 1080))
        ->toMediaCollection('logo_small');

    $html = (new GenericMail(['html_body' => '<p>Body</p>'], tenant: $this->tenant))->render();

    expect($html)->toMatch('/<img[^>]*\swidth="100"[^>]*\sheight="82"/');
});

test('a logo smaller than the box keeps its own size', function (): void {
    $this->tenant
        ->addMedia(UploadedFile::fake()->image('logo.png', 40, 20))
        ->toMediaCollection('logo_small');

    $html = (new GenericMail(['html_body' => '<p>Body</p>'], tenant: $this->tenant))->render();

    expect($html)->toMatch('/<img[^>]*\swidth="40"[^>]*\sheight="20"/');
});

test('a logo without readable dimensions renders without size attributes', function (): void {
    $this->tenant
        ->addMedia(UploadedFile::fake()->createWithContent(
            'logo.svg',
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><rect width="10" height="10"/></svg>'
        ))
        ->toMediaCollection('logo_small');

    $html = (new GenericMail(['html_body' => '<p>Body</p>'], tenant: $this->tenant))->render();

    expect($html)
        ->toContain('<img')
        ->not->toMatch('/<img[^>]*\swidth="/');
});

test('an unreadable logo file still renders the mail', function (): void {
    $this->tenant
        ->addMedia(UploadedFile::fake()->createWithContent('logo.png', ''))
        ->toMediaCollection('logo_small');

    $html = (new GenericMail(['html_body' => '<p>Body</p>'], tenant: $this->tenant))->render();

    expect($html)
        ->toContain('Body</p>')
        ->not->toMatch('/<img[^>]*\swidth="/');
});

test('a tenant without logo still renders the mail', function (): void {
    $html = (new GenericMail(['html_body' => '<p>Body</p>'], tenant: $this->tenant))->render();

    expect($html)
        ->toContain('Body</p>')
        ->not->toMatch('/<img[^>]*\swidth="/');
});
