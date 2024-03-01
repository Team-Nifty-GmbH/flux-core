<?php

namespace FluxErp\Rulesets\Contact;

use FluxErp\Rulesets\Address\CreateAddressRuleset;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Support\Arr;

class MainAddressRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return array_merge(
            [
                'main_address' => 'array',
            ],
            Arr::prependKeysWith(
                Arr::except(
                    resolve_static(CreateAddressRuleset::class, 'getRules'),
                    ['client_id', 'contact_id']
                ),
                'main_address.'
            )
        );
    }
}
