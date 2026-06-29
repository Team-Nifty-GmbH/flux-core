<?php

namespace FluxErp\Livewire\Forms\Settings;

use FluxErp\Settings\SearchSettings;
use FluxErp\Support\Livewire\Attributes\RenderAs;

class SearchSettingsForm extends SettingsForm
{
    #[RenderAs(RenderAs::TOGGLE)]
    public bool $semantic_search_enabled = false;

    public string $embedder_url = '';

    #[RenderAs(RenderAs::PASSWORD)]
    public string $embedder_api_key = '';

    public string $embedder_model = 'text-embedding-3-small';

    public int $embedder_dimensions = 1536;

    public float $semantic_ratio = 0.5;

    public function getSettingsClass(): string
    {
        return SearchSettings::class;
    }
}
