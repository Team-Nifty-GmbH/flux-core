<?php

namespace FluxErp\Rulesets\PrintLayout;

use FluxErp\Models\Client;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Support\Arr;

class CreatePrintLayoutRuleset extends FluxRuleset
{
    private function fileNames(): string
    {
        $path = flux_path('resources/views/printing');
        $dirs = array_filter(scandir($path), fn($item) => !str_starts_with($item, '.'));

        return implode(',', Arr::collapse($printing  = array_map(function ($dir) {
            $fileNames = array_filter(scandir(flux_path('resources/views/printing') . "/" . $dir),
                fn($item) => !str_starts_with($item, '.'));
            // remove extension from file names and add prefix
            $fileNames = array_map(fn($item) => 'flux::layouts.printing' . '.' . $dir . '.' . str_replace('.blade.php', '', $item), $fileNames);

            return array_values($fileNames);
        }, $dirs)));
    }

    public function rules(): array
    {
        return [
            'client_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Client::class]),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                'in:' . $this->fileNames(),

            ],
            'model_type' => [
                'required',
                'string',
                'max:255',
                app(MorphClassExists::class),
            ],
            'margin' => 'nullable|array',
            'header' => 'nullable|array',
            'footer' => 'nullable|array',
            'first_page_header' => 'nullable|array',
            'temporaryMedia' => 'array',
            'temporary_snippets' => 'array',
        ];
    }
}
