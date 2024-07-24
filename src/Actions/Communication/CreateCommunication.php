<?php

namespace FluxErp\Actions\Communication;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Models\Address;
use FluxErp\Models\Communication;
use FluxErp\Models\Pivots\Communicatable;
use FluxErp\Models\Tag;
use FluxErp\Rulesets\Communication\CreateCommunicationRuleset;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

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

        $startedAt = data_get($this->data, 'started_at');
        $endedAt = data_get($this->data, 'ended_at');

        if (is_null(data_get($this->data, 'total_time_ms')) && $startedAt && $endedAt) {
            $this->data['total_time_ms'] = Carbon::parse($endedAt)->diffInMilliseconds(Carbon::parse($startedAt));
        }

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
            $communication->attachTags(resolve_static(Tag::class, 'query')->whereIntegerInRaw('id', $tags)->get());
        }

        foreach ($attachments as $attachment) {
            $attachment['model_id'] = $communication->id;
            $attachment['model_type'] = app(Communication::class)->getMorphClass();
            $attachment['collection_name'] = 'attachments';
            $attachment['media_type'] = 'string';

            try {
                UploadMedia::make($attachment)->validate()->execute();
            } catch (ValidationException) {
            }
        }

        if ($this->data['communicatable_type'] === morph_alias(Address::class)) {
            $communication->loadMissing('communicatable.contact');
            $communication->communicatable->contact->communications()->attach($communication->id);
        }

        return $communication->fresh();
    }
}
