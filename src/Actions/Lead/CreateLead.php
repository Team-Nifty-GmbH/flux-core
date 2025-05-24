<?php

namespace FluxErp\Actions\Lead;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Lead;
use FluxErp\Models\LeadState;
use FluxErp\Models\Tag;
use FluxErp\Rulesets\Lead\CreateLeadRuleset;
use Illuminate\Support\Arr;

class CreateLead extends FluxAction
{
    public static function models(): array
    {
        return [Lead::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateLeadRuleset::class;
    }

    public function performAction(): Lead
    {
        $tags = Arr::pull($this->data, 'tags');

        $lead = app(Lead::class, ['attributes' => $this->getData()]);
        $lead->save();

        if ($tags) {
            $lead->attachTags(
                resolve_static(Tag::class, 'query')
                    ->whereIntegerInRaw('id', $tags)
                    ->get()
            );
        }

        return $lead->refresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['start'] ??= now()->format('Y-m-d');
        $this->data['end'] ??= $this->data['start'];
        $this->data['lead_state_id'] ??= LeadState::default()?->getKey();

        if (
            resolve_static(LeadState::class, 'query')
                ->whereKey($this->getData('lead_state_id'))
                ->value('is_lost')
        ) {
            $this->rules['loss_reason'] = 'required|string|min:8';
        }
    }
}
