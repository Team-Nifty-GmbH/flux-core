<?php

namespace FluxErp\Helpers\Livewire\Features;

use Livewire\Features\SupportFormObjects\Form;
use Livewire\Features\SupportFormObjects\FormObjectSynth as BaseFormObjectSynth;

class FormObjectSynth extends BaseFormObjectSynth
{
    public function hydrate($data, $meta, $hydrateChild): Form
    {
        $meta['class'] = resolve_static($meta['class'], 'class');

        return parent::hydrate($data, $meta, $hydrateChild);
    }

    function dehydrate($target, $dehydrateChild): array
    {
        $data = parent::dehydrate($target, $dehydrateChild);

        $data[1]['class'] = resolve_static($data[1]['class'], 'class');

        return $data;
    }
}
