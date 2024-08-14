<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Plugins\Install;
use FluxErp\Actions\Plugins\ToggleActive;
use FluxErp\Actions\Plugins\Uninstall;
use FluxErp\Actions\Plugins\Update;
use FluxErp\Actions\Plugins\Upload;
use FluxErp\Livewire\Forms\MediaForm;
use Illuminate\Contracts\View\View;
use Illuminate\Process\Exceptions\ProcessFailedException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Spatie\Permission\Exceptions\UnauthorizedException;
use WireUi\Traits\Actions;

#[Lazy]
class Plugins extends Component
{
    use Actions, WithFileUploads;

    public array $installed = [];

    public string $search = '';

    public MediaForm $file;

    public array $searchResult = [];

    public ?string $readme = null;

    public array $update = [];

    public bool $offerRefresh = false;

    public ?string $settingsComponent = null;

    public int $outdated = 0;

    public function mount(): void
    {
        $this->getInstalled();
    }

    public function placeholder(): View
    {
        return view('flux::livewire.placeholders.horizontal-bar');
    }

    public function render(): View
    {
        return view('flux::livewire.settings.plugins');
    }

    public function getInstalled(): void
    {
        /** @var \FluxErp\Helpers\Composer $composer */
        $composer = app('composer');
        $available = Arr::keyBy(data_get($composer->showAvailable(), 'available', []), 'name');
        $this->installed = data_get($composer->installed(true), 'installed', []);

        foreach ($available as $key => $package) {
            if (! array_key_exists($key, $this->installed)) {
                $package['offer_install'] = true;
                $package['is_flux_plugin'] = true;
                $this->installed[$key] = $package;
            }
        }

        $flux = $this->installed['team-nifty-gmbh/flux-erp'];
        $flux['is_flux_plugin'] = true;
        unset($this->installed['team-nifty-gmbh/flux-erp']);
        $this->installed = ['team-nifty-gmbh/flux-erp' => $flux] + $this->installed;
    }

    public function more(string $package): void
    {
        $packageInfo = app('composer')->show($package);
        try {
            $readme = file_get_contents($packageInfo['path'].DIRECTORY_SEPARATOR.'README.md');
            $this->readme = Str::markdown($readme);
        } catch (\Exception) {
            $this->readme = null;
        }

        $this->settingsComponent = $this->installed[$package]['settings'] ?? null;

        $this->js(<<<'JS'
            $openModal('more');
        JS);
    }

    #[Renderless]
    public function installUploaded(): void
    {
        $packages = $this->file->stagedFiles;
        foreach ($packages as $package) {
            $file = TemporaryUploadedFile::createFromLivewire(basename($package['media']));

            try {
                Upload::make(['packages' => [$file]])
                    ->checkPermission()
                    ->validate()
                    ->execute();

                $this->offerRefresh = true;
            } catch (UnauthorizedException|ValidationException $e) {
                exception_to_notifications($e, $this);

                return;
            }
        }

        $this->file->reset();

        if ($this->offerRefresh) {
            $this->getInstalled();
        }
    }

    public function updatedSearch(): void
    {
        $this->skipRender();
        $result = app('composer')->search($this->search);

        $this->searchResult = array_values(
            array_filter(
                $result,
                fn ($package) => ! in_array($package['name'], array_keys($this->installed))
                    && $package['name'] !== 'team-nifty-gmbh/flux-erp'
            )
        );

        $this->searchResult = array_map(function ($package) {
            $package['downloads'] = Number::forHumans($package['downloads'] ?? 0, abbreviate: true);
            $package['favers'] = Number::forHumans($package['favers'] ?? 0, maxPrecision: 2, abbreviate: true);

            return $package;
        }, $this->searchResult);
    }

    #[Renderless]
    public function checkForUpdates(): void
    {
        try {
            $updates = app('composer')->outdated();
        } catch (ProcessFailedException $e) {
            $this->notification()->error(__('Failed to check for updates.'));
            $this->addError('checkForUpdates', $e->getMessage());

            return;
        }

        $updates = collect($updates['installed'] ?? [])
            ->keyBy('name')
            ->toArray();

        $this->outdated = count($updates);

        foreach ($this->installed as $key => &$package) {
            if (array_key_exists($key, $updates)) {
                $package = array_merge($package, $updates[$key]);
            } else {
                unset($package['latest']);
            }
        }
    }

    #[Renderless]
    public function showChangeLog(string $package, string $version): void
    {
        $packageInfo = $this->installed[$package];
        $this->update = ['package' => $package, 'version' => $version];

        if (str_starts_with($packageInfo['source'], 'https://github.com')) {
            $changeLog = Http::withHeader('Accept', 'application/vnd.github.v3+json')
                ->get('https://api.github.com/repos/'.$package.'/releases/tags/'.$version)
                ->json('body');

            $this->update['readme'] = Str::markdown($changeLog ?? '### '.__('No changelog found'));
            $this->update['readme'] = str_replace('<a', '<a target="_blank"', $this->update['readme']);
        }

        $this->js(<<<'JS'
            $openModal('update');
        JS);
    }

    #[Renderless]
    public function updatePackages(array|string|null $packages): void
    {
        $packages = is_array($packages) ? $packages : [$packages];

        try {
            Update::make(['packages' => $packages])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\RuntimeException|UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->notification()->success(__('Packages updated successfully'));

        $this->checkForUpdates();
    }

    public function updateAll(): void
    {
        try {
            Update::make([])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\RuntimeException|UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->notification()->success(__('Packages updated successfully'));

        $this->checkForUpdates();
    }

    #[Renderless]
    public function uninstall(string|array $packages, bool $rollback = false): void
    {
        $packages = is_array($packages) ? $packages : [$packages];

        try {
            Uninstall::make(['packages' => $packages, 'rollback' => $rollback])
                ->validate()
                ->checkPermission()
                ->execute();
        } catch (\RuntimeException|UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->offerRefresh = true;
        $this->notification()
            ->success(
                __('Package :package uninstalled successfully.', ['package' => implode(',', $packages)])
            );

        if ($this->offerRefresh) {
            $this->getInstalled();
        }
    }

    #[Renderless]
    public function install(string $package): bool
    {
        try {
            Install::make(['packages' => [$package], 'migrate' => true])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->offerRefresh = true;
        $this->notification()->success(__('Package :package installed successfully.', ['package' => $package]));
        $this->getInstalled();

        return true;
    }

    public function updated($key): void
    {
        $path = explode('.', $key);

        if (($path[2] ?? false) === 'is_active') {
            $this->skipRender();
            $this->toggleActive($path[1]);
        }
    }

    public function toggleActive(string $packageName): void
    {
        try {
            ToggleActive::make(['packages' => [$packageName]])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->offerRefresh = true;
    }
}
