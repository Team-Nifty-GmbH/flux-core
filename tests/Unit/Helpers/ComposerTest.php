<?php

use FluxErp\Helpers\Composer;
use Illuminate\Filesystem\Filesystem;

test('getProcess includes HOME environment variable', function (): void {
    $composer = new Composer(new Filesystem(), base_path());

    $method = new ReflectionMethod($composer, 'getProcess');
    $process = $method->invoke($composer, ['echo', 'test']);

    $env = $process->getEnv();

    expect($env)->toHaveKey('HOME');
});

test('installedMetadata reads what composer wrote instead of asking it again', function (): void {
    $path = sys_get_temp_dir() . '/flux-composer-' . uniqid();
    mkdir($path . '/vendor/composer', 0777, true);
    file_put_contents($path . '/vendor/composer/installed.json', json_encode([
        'packages' => [
            [
                'name' => 'team-nifty-gmbh/flux-demo',
                'description' => 'A demo',
                'require' => ['team-nifty-gmbh/flux-erp' => '^1.0'],
                'require-dev' => ['pestphp/pest' => '^4.0'],
                'license' => ['MIT'],
                'version' => '1.2.3',
            ],
        ],
    ]));

    $composer = new Composer(new Filesystem(), $path);
    $metadata = (new ReflectionMethod($composer, 'installedMetadata'))->invoke($composer);

    expect($metadata)->toHaveKey('team-nifty-gmbh/flux-demo');

    $package = $metadata['team-nifty-gmbh/flux-demo'];

    expect(['team-nifty-gmbh/flux-erp' => '^1.0'])->toBe($package['requires'])
        ->and(['pestphp/pest' => '^4.0'])->toBe($package['devRequires'])
        ->and(['MIT'])->toBe($package['licenses'])
        ->and('A demo')->toBe($package['description'])
        ->and($package)->not->toHaveKey('homepage');

    (new Filesystem())->deleteDirectory($path);
});

test('installedMetadata answers with nothing when composer wrote no metadata', function (): void {
    $path = sys_get_temp_dir() . '/flux-composer-' . uniqid();
    mkdir($path, 0777, true);

    $composer = new Composer(new Filesystem(), $path);

    expect([])->toBe((new ReflectionMethod($composer, 'installedMetadata'))->invoke($composer));

    (new Filesystem())->deleteDirectory($path);
});
