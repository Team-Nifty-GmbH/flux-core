<?php

namespace FluxErp\Console\Commands\Init;

use FluxErp\Models\Language;
use Illuminate\Console\Command;

class InitLanguages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:languages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initiates Languages and fills table with data.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $locale = Language::query()
            ->where('language_code', config('app.locale'))
            ->firstOrNew();
        if (! $locale->exists) {
            $locale->fill([
                'name' => config('app.locale'),
                'iso_name' => config('app.locale'),
                'language_code' => config('app.locale'),
            ]);
            $locale->save();
        }

        $fallback = Language::query()
            ->where('language_code', config('app.fallback_locale'))
            ->firstOrNew();
        if (! $fallback->exists) {
            $fallback->fill([
                'name' => config('app.fallback_locale'),
                'iso_name' => config('app.fallback_locale'),
                'language_code' => config('app.fallback_locale'),
            ]);
            $fallback->save();
        }

        $path = resource_path() . '/init-files/languages.json';
        if (! file_exists($path)) {
            return;
        }

        $json = json_decode(file_get_contents($path), true);

        if ($json['model'] === 'Language') {
            $jsonLanguages = $json['data'];

            if ($jsonLanguages) {
                foreach ($jsonLanguages as $jsonLanguage) {
                    $jsonLanguage['name'] = __($jsonLanguage['name']);

                    // Save to database.
                    $language = Language::query()
                        ->where('language_code', $jsonLanguage['language_code'])
                        ->firstOrNew();

                    if (! $language->exists) {
                        $language->fill($jsonLanguage);
                        $language->save();
                    }
                }
            }
        }

        $this->info('Languages initiated!');
    }
}
