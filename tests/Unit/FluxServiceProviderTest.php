<?php

use function Livewire\invade;

test('boot commands filters out non-existing cached command classes', function (): void {
    $cachePath = $this->app->bootstrapPath('cache/flux-commands.php');

    $original = file_exists($cachePath) ? file_get_contents($cachePath) : null;

    try {
        // Write a cache file with one valid and one invalid command class
        file_put_contents($cachePath, sprintf(
            "<?php\nreturn %s;\n",
            var_export([
                'NonExistent\\Command\\ShouldBeFiltered',
                FluxErp\Console\Commands\FluxOptimize::class,
            ], true)
        ));

        $provider = new FluxErp\FluxServiceProvider($this->app);
        invade($provider)->bootCommands();

        // The non-existing class should not be registered as a command
        $commands = array_keys(Illuminate\Support\Facades\Artisan::all());

        expect($commands)->not->toContain('NonExistent\\Command\\ShouldBeFiltered');
    } finally {
        if ($original !== null) {
            file_put_contents($cachePath, $original);
        } elseif (file_exists($cachePath)) {
            unlink($cachePath);
        }
    }
});
