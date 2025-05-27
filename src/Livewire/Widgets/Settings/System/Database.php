<?php

namespace FluxErp\Livewire\Widgets\Settings\System;

use FluxErp\Livewire\Settings\System;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Console\PruneCommand;
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

        if ($result === 0) {
            $this->toast()->success(trim($output->fetch()))->send();
            $this->getData();
        } else {
            $this->toast()->error(trim($output->fetch()))->send();
        }
    }
}
