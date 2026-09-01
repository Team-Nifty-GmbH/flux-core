<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\Forms\Settings\SearchSettingsForm;
use FluxErp\Livewire\Support\SettingsComponent;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Renderless;

class SearchSettings extends SettingsComponent
{
    public SearchSettingsForm $searchSettingsForm;

    #[Renderless]
    public function save(): bool
    {
        $saved = parent::save();

        // Push the updated embedder config to Meilisearch so it (re-)embeds documents.
        if ($saved && config('scout.driver') === 'meilisearch') {
            Artisan::queue('flux-scout:sync-index-settings');
        }

        return $saved;
    }

    protected function getFormPropertyName(): string
    {
        return 'searchSettingsForm';
    }
}
