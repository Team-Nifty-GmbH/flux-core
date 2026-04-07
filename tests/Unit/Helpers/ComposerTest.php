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
