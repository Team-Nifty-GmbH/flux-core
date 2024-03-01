<?php

namespace FluxErp\Rulesets\Plugin;

use FluxErp\Rulesets\FluxRuleset;

class UploadPluginRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'packages' => 'required|array',
            'packages.*' => [
                'required',
                'file',
                'mimetypes:application/zip,application/x-rar-compressed,application/x-7z-compressed',
            ],
        ];
    }
}
