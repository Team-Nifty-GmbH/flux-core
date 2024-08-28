<?php

namespace FluxErp\Helpers;

use Illuminate\Foundation\Vite as BaseVite;

class Vite extends BaseVite
{
    protected function manifestPath($buildDirectory): string
    {
        return $buildDirectory . '/manifest.json';
    }

    public function content($asset, $buildDirectory = null): false|string
    {
        $buildDirectory ??= $this->buildDirectory;

        $chunk = $this->chunk($this->manifest($buildDirectory), $asset);

        $path = $buildDirectory . '/' . $chunk['file'];

        if (! is_file($path) || ! file_exists($path)) {
            throw new \Exception("Unable to locate asset file: {$path}");
        }

        return file_get_contents($path);
    }
}
