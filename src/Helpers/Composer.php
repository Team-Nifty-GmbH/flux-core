<?php

namespace FluxErp\Helpers;

use FluxErp\Enums\ComposerRepositoryTypeEnum;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Composer as BaseComposer;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class Composer extends BaseComposer
{
    public function __construct(Filesystem $files, $workingPath = null)
    {
        parent::__construct($files, $workingPath);

        if (! $this->workingPath) {
            $this->setWorkingPath(base_path());
        }
    }

    /**
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     */
    public function search(string $search, ?string $composerBinary = null): array
    {
        if (! $search) {
            return [];
        }

        $command = [
            ...$this->findComposer($composerBinary),
            'search',
            '--format=json',
            $search,
        ];

        return json_decode($this->getProcess($command)->mustRun()->getOutput(), true);
    }

    /**
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     */
    public function show(string $package, ?string $composerBinary = null): array
    {
        $command = [
            ...$this->findComposer($composerBinary),
            'show',
            '--format=json',
            $package,
        ];

        return json_decode($this->getProcess($command)->mustRun()->getOutput(), true);
    }

    public function addRepository(ComposerRepositoryTypeEnum $type, string $url, string $name): void
    {
        $this->modify(function ($composer) use ($type, $url, $name) {
            $composer['repositories'][$name] = [
                'type' => $type->value,
                'url' => $url,
            ];

            return $composer;
        });
    }

    public function removeRepository(string $name): void
    {
        $this->modify(function ($composer) use ($name) {
            unset($composer['repositories'][$name]);

            return $composer;
        });
    }

    /**
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     */
    public function showAvailable(bool $withPackagist = false, ?bool $composerBinary = null): array
    {
        $command = [
            ...$this->findComposer($composerBinary),
            'show',
            '--available',
            '--format=json',
        ];

        if (! $withPackagist) {
            // disable packagist
            $disableCommand = [
                ...$this->findComposer($composerBinary),
                'config',
                'repo.packagist',
                'false',
            ];
            $this->getProcess($disableCommand)->mustRun();
        }

        $available = json_decode($this->getProcess($command)->mustRun()->getOutput(), true);

        // enable packagist
        $enableCommand = [
            ...$this->findComposer($composerBinary),
            'config',
            '--unset',
            'repo.packagist',
        ];
        $this->getProcess($enableCommand)->mustRun();

        return $available;
    }

    /**
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     */
    public function installed(bool $direct = false, bool $dev = false, $composerBinary = null): array
    {
        $composer = json_decode(file_get_contents($this->findComposerFile()), true);
        $composerFlux = json_decode(file_get_contents(__DIR__ . '/../../composer.json'), true);

        $command = collect([
            ...$this->findComposer($composerBinary),
            'show',
            '--format=json',
        ])
            ->when(! $dev, function ($command) {
                $command->push('--no-dev');
            })
            ->when($direct, function ($command) {
                $command->push('--direct');
            })
            ->all();

        $installed = json_decode($this->getProcess($command)->mustRun()->getOutput(), true);
        $installed['installed'] = Arr::keyBy($installed['installed'], 'name');

        $uninstalled = array_filter(
            $composer['require'],
            fn ($package) => ! str_starts_with($package, 'ext-') && $package !== 'php',
            ARRAY_FILTER_USE_KEY
        );

        foreach ($installed['installed'] as $key => &$package) {
            $packageInfo = json_decode($this->getProcess([
                ...$this->findComposer($composerBinary),
                'show',
                '--format=json',
                $package['name'],
            ])->mustRun()->getOutput(), true);

            $package = array_merge($package, $packageInfo);
            unset($uninstalled[$key]);
            $package['is_active'] = ! in_array($key, $composer['extra']['laravel']['dont-discover'] ?? []);
            $package['can_uninstall'] = ! array_key_exists($key, $composerFlux['require'] ?? [])
                && $package['name'] !== 'team-nifty-gmbh/flux-erp';
            $package['is_flux_plugin'] = array_key_exists(
                'team-nifty-gmbh/flux-erp',
                $packageInfo['requires'] ?? []
            );
        }

        return $installed;
    }

    /**
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     */
    public function outdated(bool $direct = true, bool $dev = false, ?bool $composerBinary = null): array
    {
        $command = collect([
            ...$this->findComposer($composerBinary),
            'outdated',
            '--direct',
            '--format=json',
        ])
            ->when(! $dev, function ($command) {
                $command->push('--no-dev');
            })
            ->when($direct, function ($command) {
                $command->push('--direct');
            })
            ->all();

        return json_decode($this->getProcess($command)->mustRun()->getOutput(), true);
    }

    /**
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     */
    public function updatePackages(array|string|null $packages, $composerBinary = null): string
    {
        $command = collect([
            ...$this->findComposer($composerBinary),
            'update',
        ])
            ->when($packages, function ($command) use ($packages) {
                $packages = is_array($packages) ? implode(' ', $packages) : $packages;

                $command->push($packages);
            })
            ->all();

        return $this->getProcess($command)->mustRun()->getOutput();
    }

    protected function getProcess(array $command, array $env = []): Process
    {
        $defaultEnv = [
            'XDEBUG_MODE' => 'off',
        ];

        $env = array_merge(
            $env,
            $defaultEnv
        );

        return parent::getProcess($command, $env);
    }
}
