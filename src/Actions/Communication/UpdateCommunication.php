<?php

namespace FluxErp\Actions\Communication;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateCommunicationRequest;
use FluxErp\Models\Communication;
use FluxErp\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class UpdateCommunication extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateCommunicationRequest())->rules();
    }

    public static function models(): array
    {
        return [Communication::class];
    }

    public function performAction(): Model
    {
        $tags = Arr::pull($this->data, 'tags');

        $communication = Communication::query()
            ->whereKey($this->data['id'])
            ->first();

        $communication->fill($this->data);
        $communication->save();

        if (! is_null($tags)) {
            $communication->syncTags(Tag::query()->whereIntegerInRaw('id', $tags)->get());
        }

        return $communication->withoutRelations()->fresh();
    }
}
