<?php

namespace FluxErp\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Foundation\Console\KeyGenerateCommand;
use Illuminate\Foundation\Console\StorageLinkCommand;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use PDO;
use PDOException;

class Init extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:init
                            {--filter= : filter which sync should be executed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initiate the database with actual production data.';

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
        $this->call(StorageLinkCommand::class);

        if (! File::exists('.env')) {
            copy('.env.example', '.env');
            if (! config('app.key')) {
                $this->call(KeyGenerateCommand::class);
            }
            $envFilePath = App::environmentFilePath();
            $this->info('The following environment file is used: »' . $envFilePath . '«');

            $content = file_get_contents($envFilePath);

            // Ask for default locale
            $locales['APP_LOCALE'] = $this->ask(__('Enter your default locale'), 'en_US');
            $locales['APP_LOCALE_FALLBACK'] = $this->ask(__('Enter your your fallback locale'), 'en_US');

            foreach ($locales as $key => $value) {
                $content = $this->setEnvVariable($content, $key, $value)[0];
            }

            // Ask for Database Credentials
            while (true) {
                $dbCredentials['DB_HOST'] = $this->ask(__('Enter database host'), 'localhost');
                $dbCredentials['DB_PORT'] = $this->ask(__('Enter database port'), '3306');

                while (true) {
                    $dbCredentials['DB_USERNAME'] = $this->ask(__('Enter database username'));
                    if (! empty($dbCredentials['DB_USERNAME'])) {
                        break;
                    }

                    $this->error(__('Database username is required!'));
                }

                while (true) {
                    $dbCredentials['DB_PASSWORD'] = $this->ask(__('Enter database password'));
                    if (! empty($dbCredentials['DB_PASSWORD'])) {
                        break;
                    }

                    $this->error(__('Database password is required!'));
                }

                while (true) {
                    $dbCredentials['DB_DATABASE'] = $this->ask(__('Enter database name'));
                    if (! empty($dbCredentials['DB_DATABASE'])) {
                        break;
                    }

                    $this->error(__('Database name is required!'));
                }

                // check database connection with new credentials
                try {
                    new PDO('mysql:host=' . $dbCredentials['DB_HOST'] . ':' . $dbCredentials['DB_PORT'] .
                        ';dbname=' . $dbCredentials['DB_DATABASE'],
                        $dbCredentials['DB_USERNAME'],
                        $dbCredentials['DB_PASSWORD']);

                    $this->info(__('Database Connection successfull!'));
                    break;
                } catch (PDOException $e) {
                    $this->error($e->getMessage());
                }
            }

            foreach ($dbCredentials as $key => $value) {
                $content = $this->setEnvVariable($content, $key, $value)[0];
            }

            $this->writeFile($envFilePath, $content);
            $this->call('config:cache');
            $this->call('config:clear');
        } else {
            if (! $this->confirmToProceed()) {
                return;
            }

            $envFilePath = App::environmentFilePath();
            $this->info('The following environment file is used: »' . $envFilePath . '«');

            $content = file_get_contents($envFilePath);
        }

        $filter = $this->option('filter');
        if (! empty($filter)) {
            try {
                $this->call($filter . ':init', []);
            } catch (\Exception $e) {
                $this->error($filter . ' not found');
            }
        } else {
            // Most crucial tables for all purposes.
            $this->call('migrate');
            $this->call('init:languages');
            $this->call('init:currencies');
            $this->call('init:countries');
            $this->call('init:country-regions');
            $this->call('init:permissions');
            $this->call('init:address-types');

            $this->setEnvVariable($content, 'APP_DEBUG', false);
            $this->setEnvVariable($content, 'APP_ENV', 'production');
            $this->writeFile($envFilePath, $content);
        }
    }

    /**
     * Set or update env-variable.
     *
     * @param  string  $envFileContent  Content of the .env file.
     * @param  string  $key  Name of the variable.
     * @param  string  $value  Value of the variable.
     * @return array [string newEnvFileContent, bool isNewVariableSet].
     */
    private function setEnvVariable(string $envFileContent, string $key, string $value): array
    {
        $oldPair = $this->readKeyValuePair($envFileContent, $key);

        // Wrap values that have a space or equals in quotes to escape them
        if (preg_match('/\s/', $value) || str_contains($value, '=')) {
            $value = '"' . $value . '"';
        }

        $newPair = $key . '=' . $value;

        // For existed key.
        if ($oldPair !== null) {
            $replaced = preg_replace('/^' . preg_quote($oldPair, '/') . '$/uimU', $newPair, $envFileContent);

            return [$replaced, false];
        }

        // For a new key.
        return [$envFileContent . "\n" . $newPair . "\n", true];
    }

    /**
     * Read the "key=value" string of a given key from an environment file.
     * This function returns original "key=value" string and doesn't modify it.
     *
     * @return string|null Key=value string or null if the key is not exists.
     */
    private function readKeyValuePair(string $envFileContent, string $key): ?string
    {
        // Match the given key at the beginning of a line
        if (preg_match("#^ *{$key} *= *[^\r\n]*$#uimU", $envFileContent, $matches)) {
            return $matches[0];
        }

        return null;
    }

    /**
     * Overwrite the contents of a file.
     */
    private function writeFile(string $path, string $contents): void
    {
        file_put_contents($path, $contents, LOCK_EX);
    }
}
