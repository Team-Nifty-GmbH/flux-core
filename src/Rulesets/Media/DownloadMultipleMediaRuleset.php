<?php

namespace FluxErp\Rulesets\Media;

use FluxErp\Models\Media;
use FluxErp\Models\MediaFolder;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DownloadMultipleMediaRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'file_name' => [
                'required',
                'string',
            ],

            'media' => [
                'required_without:media_folders',
                'array',
            ],
            'media.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Media::class]),
            ],

            'media_folders' => [
                'required_without:media',
                'exclude_with:media',
                'array',
            ],
            'media_folders.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => MediaFolder::class]),
            ],
            'with_subfolders' => 'required_with:media_folders|exclude_with:media|boolean',
        ];
    }
}
