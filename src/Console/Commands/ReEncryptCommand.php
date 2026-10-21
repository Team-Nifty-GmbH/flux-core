<?php

namespace FluxErp\Console\Commands;

use Composer\Autoload\ClassLoader;
use FluxErp\Settings\SettingsManager;
use Illuminate\Console\Command;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Throwable;

class ReEncryptCommand extends Command
{
    protected Encrypter $current;

    protected $description = 'Re-encrypt every encrypted model attribute and encrypted setting with the current APP_KEY.';

    protected bool $failed = false;

    protected $signature = 'flux:re-encrypt
        {--dry-run : Only count the values still encrypted with a previous key}
        {--chunk=500 : Rows per chunk}';

    public function handle(): int
    {
        $this->current = new Encrypter(app('encrypter')->getKey(), config('app.cipher'));

        foreach ($this->encryptedColumns() as [$connection, $table, $keyName, $column]) {
            $this->reEncryptColumn($connection, $table, $keyName, $column);
        }

        $this->reEncryptSettings();

        if (! $this->option('dry-run') && config('settings.cache.enabled')) {
            $this->call('settings:clear-cache');
        }

        return $this->failed ? self::FAILURE : self::SUCCESS;
    }

    protected function encryptedColumns(): Collection
    {
        return $this->models()
            ->flatMap(function (string $class): array {
                /** @var Model $model */
                $model = app($class);

                return collect($model->getCasts())
                    ->filter(fn (mixed $cast): bool => is_string($cast)
                        && (
                            str_starts_with($cast, 'encrypted')
                            || str_starts_with(class_basename(Str::before($cast, ':')), 'AsEncrypted')
                        )
                    )
                    ->keys()
                    ->map(fn (string $column): array => [
                        $model->getConnectionName(),
                        $model->getTable(),
                        $model->getKeyName(),
                        $column,
                    ])
                    ->all();
            })
            ->unique(fn (array $column): string => implode('.', $column))
            ->filter(fn (array $column): bool => Schema::connection($column[0])->hasColumn($column[1], $column[3]))
            ->values();
    }

    // ponytail: packages do not morph map every model (FinapiUser, DatevOauthToken), so the Models
    // directories of all autoloaded namespaces are scanned as well
    protected function models(): Collection
    {
        $classes = collect(Relation::morphMap())->values();

        foreach (ClassLoader::getRegisteredLoaders() as $loader) {
            foreach ($loader->getPrefixesPsr4() as $namespace => $directories) {
                foreach ($directories as $directory) {
                    if (! is_dir($directory . '/Models')) {
                        continue;
                    }

                    foreach (Finder::create()->in($directory . '/Models')->files()->name('*.php') as $file) {
                        $classes->push(
                            $namespace . 'Models\\'
                            . str_replace('/', '\\', Str::beforeLast($file->getRelativePathname(), '.php'))
                        );
                    }
                }
            }
        }

        return $classes->unique()
            ->filter(function (string $class): bool {
                try {
                    return class_exists($class)
                        && is_subclass_of($class, Model::class)
                        && (new ReflectionClass($class))->isInstantiable();
                } catch (Throwable) {
                    return false;
                }
            })
            ->values();
    }

    protected function reEncrypt(mixed $value, string $label): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        try {
            $this->current->decryptString($value);

            return null;
        } catch (DecryptException) {
        }

        try {
            return $this->current->encryptString(app('encrypter')->decryptString($value));
        } catch (DecryptException) {
            $this->error($label . ': no key decrypts this value, left unchanged');
            $this->failed = true;

            return null;
        }
    }

    protected function reEncryptColumn(?string $connection, string $table, string $keyName, string $column): void
    {
        $count = 0;

        DB::connection($connection)
            ->table($table)
            ->whereNotNull($column)
            ->select([$keyName, $column])
            ->chunkById(
                (int) $this->option('chunk'),
                function (Collection $rows) use ($connection, $table, $keyName, $column, &$count): void {
                    foreach ($rows as $row) {
                        $reEncrypted = $this->reEncrypt(
                            $row->{$column},
                            $table . '.' . $column . ' id ' . $row->{$keyName}
                        );

                        if (is_null($reEncrypted)) {
                            continue;
                        }

                        $count++;

                        if (! $this->option('dry-run')) {
                            DB::connection($connection)
                                ->table($table)
                                ->where($keyName, $row->{$keyName})
                                ->update([$column => $reEncrypted]);
                        }
                    }
                },
                $keyName
            );

        $this->line($table . '.' . $column . ': ' . $count . ' with a previous key');
    }

    protected function reEncryptSettings(): void
    {
        $table = config('settings.repositories.database.table') ?? 'settings';

        foreach (app(SettingsManager::class)->all() as $settingsClass) {
            foreach ($settingsClass::encrypted() as $name) {
                $row = DB::table($table)
                    ->where('group', $settingsClass::group())
                    ->where('name', $name)
                    ->first(['id', 'payload']);

                if (! $row) {
                    continue;
                }

                $label = $table . '.' . $settingsClass::group() . '.' . $name;
                $reEncrypted = $this->reEncrypt(json_decode($row->payload), $label);

                $this->line($label . ': ' . (int) filled($reEncrypted) . ' with a previous key');

                if (filled($reEncrypted) && ! $this->option('dry-run')) {
                    DB::table($table)->where('id', $row->id)->update(['payload' => json_encode($reEncrypted)]);
                }
            }
        }
    }
}
