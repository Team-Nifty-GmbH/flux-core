<?php

namespace FluxErp\Actions\ContactOption;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateContactOptionRequest;
use FluxErp\Models\ContactOption;
use Illuminate\Database\Eloquent\Model;

class UpdateContactOption extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateContactOptionRequest())->rules();
    }

    public static function models(): array
    {
        return [ContactOption::class];
    }

    public function performAction(): Model
    {
        $contactOption = ContactOption::query()
            ->whereKey($this->data['id'])
            ->first();

        $contactOption->fill($this->data);
        $contactOption->save();

        return $contactOption->withoutRelations()->fresh();
    }
}
