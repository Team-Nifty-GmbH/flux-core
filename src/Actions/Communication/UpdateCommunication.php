<?php

namespace FluxErp\Actions\Communication;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Address;
use FluxErp\Models\Communication;
use FluxErp\Models\Contact;
use FluxErp\Models\Tag;
use FluxErp\Rulesets\Communication\UpdateCommunicationRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class UpdateCommunication extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return UpdateCommunicationRuleset::class;
    }

    public static function models(): array
    {
        return [Communication::class];
    }

    public function performAction(): Model
    {
        $tags = Arr::pull($this->data, 'tags');
        $communicatables = Arr::pull($this->data, 'communicatables');

        /** @var Communication $communication */
        $communication = resolve_static(Communication::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $startedAt = data_get($this->data, 'started_at');
        $endedAt = data_get($this->data, 'ended_at');

        if (is_null(data_get($this->data, 'total_time_ms')) && $startedAt && $endedAt) {
            $this->data['total_time_ms'] = Carbon::parse($endedAt)->diffInMilliseconds(Carbon::parse($startedAt));
        }

        $communication->fill($this->data);
        $communication->save();

        if (! is_null($communicatables)) {
            $existing = array_filter($communicatables, fn (array $item) => ! is_null(data_get($item, 'id')));
            $new = array_filter($communicatables, fn (array $item) => is_null(data_get($item, 'id')));

            $communication->communicatables()
                ->whereIntegerNotInRaw('id', array_column($existing, 'id'))
                ->delete();

            if ($new) {
                $communication->communicatables()->createMany($new);
            }

            // ensure that all communications that are attached to an address are also attached to the contact
            $communication->communicatables()
                ->where('communicatable_type', morph_alias(Address::class))
                ->with('communicatable.contact')
                ->get(['communicatable_id', 'communicatable_type'])
                ->pluck('communicatable.contact')
                ->unique()
                ->each(fn (Contact $contact) => $contact->communications()->syncWithoutDetaching($communication->id));
        }

        if (! is_null($tags)) {
            $communication->syncTags(
                resolve_static(Tag::class, 'query')
                    ->whereIntegerInRaw('id', $tags)
                    ->get(),
            );
        }

        return $communication->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
        $model = resolve_static(Communication::class, 'query')
            ->whereKey(data_get($this->data, 'id'))
            ->first(['started_at', 'ended_at']);

        $this->data['started_at'] ??= $model->started_at;
        $this->data['ended_at'] ??= $model->ended_at;
    }
}
