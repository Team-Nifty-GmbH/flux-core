<?php

namespace FluxErp\Actions\Plugins;

use FluxErp\Actions\FluxAction;
use FluxErp\Enums\ComposerRepositoryTypeEnum;
use FluxErp\Http\Requests\PluginUploadRequest;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Upload extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new PluginUploadRequest())->rules();
    }

    public static function models(): array
    {
        return [];
    }

    public function performAction(): bool
    {
        $composer = app('composer');
        $composer->addRepository(ComposerRepositoryTypeEnum::path, './packages/*', 'packages-local');
        $composer->addRepository(ComposerRepositoryTypeEnum::path, './packages/*/*', 'packages-uploaded');

        $packages = [];
        foreach ($this->data['packages'] as $package) {
            /** @var \Illuminate\Http\UploadedFile $package */
            $zip = new \ZipArchive();
            $res = $zip->open($package->getRealPath());
            if ($res === true) {
                // get content of composer.json before extracting
                $fileIndex = $zip->locateName('composer.json', \ZipArchive::FL_NODIR);
                if ($fileIndex === false) {
                    continue;
                }

                $content = $zip->getFromIndex($fileIndex);
                $composerJson = json_decode($content, true);
                $vendor = Str::before($composerJson['name'], '/');
                $packageName = Str::after($composerJson['name'], '/');
                $packagePath = base_path('packages' . DIRECTORY_SEPARATOR . $vendor . DIRECTORY_SEPARATOR . $packageName);
                if (File::exists($packagePath)) {
                    File::deleteDirectory($packagePath);
                }

                File::ensureDirectoryExists($packagePath);

                $path = Str::beforeLast($zip->getNameIndex($fileIndex), 'composer.json');

                $zip->extractTo($packagePath);
                $zip->close();

                if ($path) {
                    foreach (File::files($packagePath . DIRECTORY_SEPARATOR . $path, true) as $file) {
                        File::move($file->getRealPath(), $packagePath . DIRECTORY_SEPARATOR . $file->getFilename());
                    }

                    foreach (File::directories($packagePath . DIRECTORY_SEPARATOR . $path) as $dir) {
                        File::moveDirectory($dir, $packagePath . DIRECTORY_SEPARATOR . basename($dir));
                    }
                }

                $packages[] = $composerJson['name'];
            }

            File::delete($package->getRealPath());
        }

        return Install::make([
            'packages' => $packages,
            'migrate' => true,
            'options' => ['--prefer-source'],
        ])
            ->execute();
    }
}
