<?php

namespace FluxErp\Livewire\Support;

use FluxErp\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;
use TeamNiftyGmbH\DataTable\Helpers\Icon;

abstract class Activities extends Component
{
    use WithPagination;

    #[Locked]
    public array $activities = [];

    #[Modelable]
    public ?int $modelId = null;

    public int $page = 1;

    public int $perPage = 15;

    public int $total = 0;

    protected string $modelType;

    public function render(): View|Factory|Application
    {
        if (! $this->activities && $this->modelId) {
            $this->loadData();
        }

        return view('flux::livewire.support.activities');
    }

    public function loadData(bool $forceRender = false): void
    {
        if (! $this->modelType || ! $this->modelId) {
            return;
        }

        $activities = resolve_static($this->modelType, 'query')
            ->whereKey($this->modelId)
            ->firstOrFail()
            ->activitiesAsSubject()
            ->with('causer:id,name')
            ->latest('id')
            ->paginate(perPage: $this->perPage * $this->page);

        $this->perPage = $activities->perPage();
        $this->total = $activities->total();

        $this->activities = $activities
            ->map(function (Activity $item) {
                $itemArray = $item->toArray();
                $itemArray['causer']['name'] = $item->causer?->getLabel() ?: __('Unknown');
                $itemArray['causer']['avatar_url'] = $item->causer?->getAvatarUrl() ?: Icon::make('user')->getUrl();
                $itemArray['event'] = __($item->event);
                $changes = auth()->user() instanceof User
                    ? ($item->attribute_changes?->toArray() ?? [])
                    : ['old' => [], 'attributes' => []];

                // Translate attribute names to human-readable labels
                foreach (['old', 'attributes'] as $changeKey) {
                    if (isset($changes[$changeKey])) {
                        $changes[$changeKey] = collect($changes[$changeKey])
                            ->mapWithKeys(fn (mixed $value, string $key) => [__(Str::headline($key)) => $value])
                            ->toArray();
                    }
                }

                $itemArray['properties'] = $changes;

                return $itemArray;
            })
            ->toArray();
    }

    #[Renderless]
    public function loadMore(): void
    {
        $this->page++;
        $this->loadData();
    }
}
