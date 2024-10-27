<?php

namespace FluxErp\Actions\Communication;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Models\Address;
use FluxErp\Models\Communication;
use FluxErp\Models\Contact;
use FluxErp\Models\Tag;
use FluxErp\Rulesets\Communication\CreateCommunicationRuleset;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class CreateCommunication extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return CreateCommunicationRuleset::class;
    }

    public static function models(): array
    {
        return [Communication::class];
    }

    public function performAction(): Communication
    {
        $attachments = Arr::pull($this->data, 'attachments', []);
        $tags = Arr::pull($this->data, 'tags');
        $communicatables = Arr::pull($this->data, 'communicatables');

        $startedAt = data_get($this->data, 'started_at');
        $endedAt = data_get($this->data, 'ended_at');

        if (is_null(data_get($this->data, 'total_time_ms')) && $startedAt && $endedAt) {
            $this->data['total_time_ms'] = Carbon::parse($endedAt)->diffInMilliseconds(Carbon::parse($startedAt));
        }

        $communication = app(Communication::class, ['attributes' => $this->data]);
        $communication->save();

        if ($communicatables) {
            $communication->communicatables()->createMany($communicatables);

            $communication->communicatables()
                ->where('communicatable_type', morph_alias(Address::class))
                ->with('communicatable.contact')
                ->get(['communicatable_id', 'communicatable_type'])
                ->pluck('communicatable.contact')
                ->unique()
                ->each(fn (Contact $contact) => $contact->communications()->syncWithoutDetaching($communication->id));
        }

        if ($tags) {
            $communication->attachTags(resolve_static(Tag::class, 'query')
                ->whereIntegerInRaw('id', $tags)
                ->get());
        }

        foreach ($attachments as $attachment) {
            $attachment['model_id'] = $communication->id;
            $attachment['model_type'] = morph_alias(Communication::class);
            $attachment['collection_name'] = 'attachments';
            $attachment['media_type'] = 'string';

            try {
                UploadMedia::make($attachment)->validate()->execute();
            } catch (ValidationException) {
            }
        }

        return $communication->fresh();
    }
}
