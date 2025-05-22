<?php

namespace FluxErp\Actions\Lead;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Lead;
use FluxErp\Models\LeadState;
use FluxErp\Models\Tag;
use FluxErp\Rulesets\Lead\UpdateLeadRuleset;
use Illuminate\Support\Arr;

class UpdateLead extends FluxAction
{
    public static function models(): array
    {
        return [Lead::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateLeadRuleset::class;
    }

    public function performAction(): Lead
    {
        $tags = Arr::pull($this->data, 'tags');

        $updateLead = resolve_static(Lead::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();
        $updateLead->fill($this->getData());
        $updateLead->save();

        if (! is_null($tags)) {
            $updateLead->syncTags(
                resolve_static(Tag::class, 'query')
                    ->whereIntegerInRaw('id', $tags)
                    ->get()
            );
        }

        return $updateLead->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
        if (
            resolve_static(LeadState::class, 'query')
                ->whereKey(
                    $this->getData('lead_state_id')
                        ?? resolve_static(Lead::class, 'query')
                            ->whereKey($this->getData('id'))
                            ->value('lead_state_id')
                )
                ->value('is_loss')
        ) {
            $this->rules['loss_reason'] = 'required|string|min:8';
        }
    }
}
