<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;

beforeEach(function (): void {
    config([
        'broadcasting.connections.mercure.public_url' => 'https://flux.example.com/.well-known/mercure',
    ]);

    $this->app->instance('request', Request::create('https://flux.example.com/'));
});

test('the browser listens on the connection the instance broadcasts with', function (string $default, array $combined, string $expected): void {
    config([
        'broadcasting.default' => $default,
        'broadcasting.connections.combined.connections' => $combined,
    ]);

    expect(Blade::render('<x-flux::layouts.head.head />'))
        ->toMatch('/name="ws-broadcaster"\s+content="' . $expected . '"/');
})->with([
    'only reverb' => ['reverb', [], 'reverb'],
    'only mercure' => ['mercure', [], 'mercure'],
    'mercure and reverb' => ['combined', ['mercure', 'reverb'], 'mercure'],
    'combined without mercure' => ['combined', ['reverb'], 'reverb'],
]);

test('the browser knows where the mercure hub is', function (): void {
    config(['broadcasting.default' => 'mercure']);

    expect(Blade::render('<x-flux::layouts.head.head />'))
        ->toMatch('#name="ws-mercure-hub"\s+content="https://flux.example.com/.well-known/mercure"#');
});

test('a combined instance served under another domain listens on reverb', function (string $default, array $combined, string $expected): void {
    config([
        'broadcasting.default' => $default,
        'broadcasting.connections.combined.connections' => $combined,
    ]);
    $this->app->instance('request', Request::create('https://connect.example.com/'));

    expect(Blade::render('<x-flux::layouts.head.head />'))
        ->toMatch('/name="ws-broadcaster"\\s+content="' . $expected . '"/');
})->with([
    'mercure and reverb' => ['combined', ['mercure', 'reverb'], 'reverb'],
    'only mercure' => ['mercure', [], 'mercure'],
]);
