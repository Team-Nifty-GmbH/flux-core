<?php

namespace FluxErp\Actions\Communication;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Models\Communication;
use FluxErp\Models\Pivots\Communicatable;
use FluxErp\Models\Tag;
use FluxErp\Rulesets\Communication\CreateCommunicationRuleset;
use Illuminate\Support\Arr;

class CreateCommunication extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateCommunicationRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Communication::class];
    }

    public function performAction(): Communication
    {
        $attachments = Arr::pull($this->data, 'attachments', []);
        $tags = Arr::pull($this->data, 'tags');

        $communication = app(Communication::class, ['attributes' => $this->data]);
        $communication->save();

        $communicatable = app(Communicatable::class, [
            'attributes' => [
                'communicatable_type' => $this->data['communicatable_type'],
                'communicatable_id' => $this->data['communicatable_id'],
                'communication_id' => $communication->id,
            ],
        ]);
        $communicatable->save();

        if ($tags) {
            $communication->attachTags(app(Tag::class)->query()->whereIntegerInRaw('id', $tags)->get());
        }

        foreach ($attachments as $attachment) {
            $attachment['model_id'] = $communication->id;
            $attachment['model_type'] = app(Communication::class)->getMorphClass();
            $attachment['collection_name'] = 'attachments';
            $attachment['media_type'] = 'string';

            UploadMedia::make($attachment)->execute();
        }

        return $communication->fresh();
    }
}
