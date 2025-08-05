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

        $lead = resolve_static(Lead::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();
        $lead->fill($this->getData());
        $lead->save();

        if (! is_null($tags)) {
            $lead->syncTags(
                resolve_static(Tag::class, 'query')
                    ->whereIntegerInRaw('id', $tags)
                    ->get()
            );
        }

        return $lead->withoutRelations()->fresh();
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
                ->value('is_lost')
        ) {
            $this->rules['loss_reason'] = [
                'required_without:lead_loss_reason_id',
                'string',
                'nullable',
                'min:8',
            ];
        }
    }
}
