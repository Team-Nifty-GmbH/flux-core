<?php

namespace FluxErp\Console\Commands\Init;

use FluxErp\Jobs\ArtisanJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class InitEnv extends Command
{
    protected $description = 'Sets the .env file to the correct values for flux';

    protected $signature = 'flux:init-env
        {keyValues? : A comma-seperated list of key:value that should be set}
        {--use-default : Use the flux default env values}';

    public function handle(): void
    {
        if (File::missing($env = app()->environmentFile()) || Cache::get('flux:env:initialized', false)) {
            return;
        }

        $contents = File::get($env);

        $input = $this->argument('keyValues');

        $keyValues = [];
        if ($input) {
            $pairs = explode(',', $input);

            foreach ($pairs as $pair) {
                $exploded = explode(':', $pair);
                $key = array_shift($exploded);
                $value = implode(':', $exploded);
                $keyValues[$key] = $this->formatEnvValue($value);
            }
        } else {
            $this->info('No key-value pairs provided.');
        }

        if ($this->option('use-default') ?? false) {
            $keyValues = array_merge($this->fluxDefault(), $keyValues);
        }

        $keyValues = array_filter($keyValues, fn ($key) => is_string($key), ARRAY_FILTER_USE_KEY);
        $keyValues = array_change_key_case($keyValues, CASE_UPPER);

        foreach ($keyValues as $key => $value) {
            if (! preg_match('/' . $key . '=.*/', $contents)) {
                $contents .= PHP_EOL . $key . '=' . $value;
            } else {
                $contents = preg_replace(
                    '/' . $key . '=.*/',
                    $key . '=' . $value,
                    $contents
                );
            }
        }

        File::put($env, $contents);
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        // check if a key starting with "pusher" was changed
        $pusherChanged = false;
        foreach ($keyValues as $key => $value) {
            if (Str::startsWith($key, 'REVERB')) {
                $pusherChanged = true;
                break;
            }
        }

        if ($pusherChanged) {
            ArtisanJob::dispatch('reverb:restart');

            $restart = 0;
            while ($restart !== 0) {
                $restart = Cache::get('laravel:reverb:restart', 0);
            }
        }

        Artisan::call('queue:restart');

        Cache::forever('flux:env:initialized', true);
    }

    protected function fluxDefault(): array
    {
        return [
            'app_env' => 'production',
            'app_debug' => 'false',
            'log_channel' => 'database',
            'broadcast_connection' => 'reverb',
            'cache_store' => 'redis',
            'cache_prefix' => uniqid() . '_',
            'queue_connection' => 'redis',
            'session_driver' => 'redis',
            'reverb_app_id' => random_int(100_000, 999_999),
            'reverb_app_key' => Str::lower(Str::random(20)),
            'reverb_app_secret' => Str::lower(Str::random(20)),
            'reverb_scheme' => 'http',
            'reverb_host' => 'localhost',
            'reverb_port' => 8080,
            'scout_driver' => 'meilisearch',
            'scout_prefix' => uniqid() . '_',
        ];
    }

    private function formatEnvValue($string): string
    {
        if (preg_match('/\s|[#;\'"\\\\]|[\x00-\x1F\x7F]/', $string)) {
            // Add single quotes around the string
            return "'" . str_replace("'", "'\"'\"'", $string) . "'";
        }

        return $string;
    }
}
