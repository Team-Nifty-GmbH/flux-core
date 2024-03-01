<?php

namespace FluxErp\Actions\FormBuilderResponse;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderResponse;
use FluxErp\Rulesets\FormBuilderResponse\DeleteFormBuilderResponseRuleset;

class DeleteFormBuilderResponse extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteFormBuilderResponseRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [FormBuilderResponse::class];
    }

    public function performAction(): ?bool
    {
        return app(FormBuilderResponse::class)->query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
