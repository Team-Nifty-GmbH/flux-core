<?php

namespace FluxErp\Actions\Media;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Media;
use FluxErp\Rulesets\Media\UpdateMediaRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UpdateMedia extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateMediaRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Media::class];
    }

    public function performAction(): Model
    {
        /** @var Media $media */
        $media = resolve_static(Media::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $currentFileName = Str::beforeLast($media->file_name, '.');
        $paths = [];
        if (data_get($this->data, 'file_name')) {
            $this->data['file_name'] = Str::finish($this->data['file_name'], '.' . $media->extension);

            $paths[] = $media->getPath();

            foreach ($media->getGeneratedConversions() as $conversion => $generated) {
                $paths[] = $media->getPath($conversion);
            }
        }

        $media->fill($this->data);
        $media->save();

        if ($paths) {
            foreach ($paths as $path) {
                rename($path, str_replace($currentFileName, Str::beforeLast($media->file_name, '.'), $path));
            }
        }

        return $media->withoutRelations();
    }
}
