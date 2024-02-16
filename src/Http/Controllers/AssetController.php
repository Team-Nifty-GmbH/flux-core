<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Facades\Asset;
use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\File;
use Livewire\Drawer\Utils;

use function Livewire\invade;

class AssetController extends Controller
{
    public function __invoke(string $filename)
    {
        $path = Asset::path($filename);

        if (! is_file($path) || ! file_exists($path)) {
            abort(404);
        }

        if (invade(app(Vite::class))->isCssPath($path)) {
            $mimeType = 'text/css';
        } else {
            $mimeType = match (pathinfo($path, PATHINFO_EXTENSION)) {
                'js' => 'application/javascript',
                default => File::mimeType($path),
            };
        }

        return Utils::pretendResponseIsFile($path, $mimeType);
    }
}
