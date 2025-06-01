<?php

namespace FluxErp\Livewire\Widgets\Settings\System;

use FluxErp\Console\Commands\PruneCommand;
use FluxErp\Livewire\Settings\System;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Console\ShowCommand;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Symfony\Component\Console\Output\BufferedOutput;

class Database extends Component
{
    use Actions, Widgetable;

    public ?string $connection = null;

    public ?string $driver = null;

    public ?string $host = null;

    public ?array $platform = null;

    public ?string $port = null;

    public ?array $tables = null;

    public static function dashboardComponent(): array|string
    {
        return System::class;
    }

    public static function getDefaultHeight(): int
    {
        return 2;
    }

    public static function getDefaultOrderColumn(): int
    {
        return 0;
    }

    public static function getDefaultOrderRow(): int
    {
        return 1;
    }

    public static function getDefaultWidth(): int
    {
        return 2;
    }

    public function mount(): void
    {
        $this->getData();
    }

    public function render(): View
    {
        return view('flux::livewire.widgets.settings.system.database');
    }

    #[Renderless]
    public function getData(): void
    {
        Artisan::call(
            ShowCommand::class,
            [
                '--json' => true,
                '--counts' => true,
            ],
            $output = new BufferedOutput()
        );

        $tableDetails = json_decode($output->fetch(), true);

        $this->fill([
            'connection' => config('database.default'),
            'driver' => config('database.connections.' . config('database.default') . '.driver'),
            'host' => config('database.connections.' . config('database.default') . '.host'),
            'port' => config('database.connections.' . config('database.default') . '.port'),
            'platform' => data_get($tableDetails, 'platform'),
            'tables' => collect(data_get($tableDetails, 'tables'))
                ->unique('schema_qualified_name')
                ->map(function (array $table) {
                    $table['size_human'] = Number::fileSize(data_get($table, 'size'));
                    $table['rows_human'] = Number::format(data_get($table, 'rows'));
                    $table['database'] = Str::before(data_get($table, 'schema_qualified_name'), '.');

                    return Arr::only(
                        $table,
                        [
                            'size',
                            'rows',
                            'size_human',
                            'rows_human',
                            'database',
                            'schema_qualified_name',
                            'table',
                        ]
                    );
                })
                ->sortBy('schema_qualified_name')
                ->toArray(),
        ]);
    }

    public function pruneDatabase(): void
    {
        $result = Artisan::call(PruneCommand::class, [], $output = new BufferedOutput());

        collect(explode("\n", $output->fetch()))
            ->filter(fn (string $line) => ! blank(trim($line))
                && ! str_contains($line, 'No prunable ')
                && ! str_contains($line, 'INFO  Pruning')
            )
            ->map(
                function (string $line): string {
                    $modelClass = Str::of($line)->before(' .')->trim();

                    return Str::of($line)
                        ->replace($modelClass, __(Str::headline(morph_alias($modelClass))))
                        ->replace('.', '')
                        ->trim();
                }
            )
            ->values()
            ->each(fn (string $line) => $this->sendToast($line, (bool) $result));
    }

    protected function sendToast(string $text, bool $isError): void
    {
        $toast = $this->toast()->persistent();

        if ($isError) {
            $toast->error($text);
        } else {
            $toast->success($text);
        }

        $toast->send();
    }
}
