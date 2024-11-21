<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Traits\Scout\Searchable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Throwable;

class Search extends Component
{
    public ?string $model = null;
    public array $availableAttributes = [];

    public ?array $searchableAttributes = null;

    public ?string $keyName = null;

    public array $settings = [];

    public function render(): View
    {
        return view(
            'flux::livewire.settings.search',
            [
                'models' => collect(Relation::morphMap())
                    ->filter(fn (string $model) => in_array(Searchable::class, class_uses_recursive($model)))
            ]
        );
    }

    public function select(string $model): void
    {
        $this->model = $model;
        $modelInfo = model_info_all()->firstWhere('class', morphed_model($model));
        $modelInstance = app(morphed_model($model));
        $this->searchableAttributes[] = $modelInstance->getScoutKeyName();

        try {
            $index = $modelInstance->searchableUsing()->getIndex($modelInstance->searchableAs());
            $this->settings = Arr::except($index->getSettings(), ['typoTolerance', 'faceting', 'synonyms']);
            $this->searchableAttributes = array_keys(data_get($index->stats(), 'fieldDistribution'));
        } catch (Throwable) {
            //throw $th;
        }

        $this->availableAttributes = $modelInfo
            ->attributes
            ->pluck('name')
            ->merge($this->searchableAttributes)
            ->unique()
            ->toArray();
    }

    #[Renderless]
    public function addStringToSetting(string $string, string $settingKey): void
    {
        data_set(
            $this->settings,
            $settingKey,
            collect(data_get($this->settings, $settingKey, []))
                ->push($string)
                ->unique()
                ->values()
                ->toArray()
        );
    }

    #[Renderless]
    public function removeStringFromSetting(string $string, string $settingKey): void
    {
        data_set(
            $this->settings,
            $settingKey,
            collect(data_get($this->settings, $settingKey, []))
                ->reject(fn (string $item) => $item === $string)
                ->values()
                ->toArray()
        );
    }
}
