<?php

namespace FluxErp\Livewire\Support;

use FluxErp\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;
use TeamNiftyGmbH\DataTable\Helpers\Icon;

abstract class Activities extends Component
{
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
            $this->loadPage(page: 1, perPage: $this->perPage * $this->page);
        }

        return view('flux::livewire.support.activities');
    }

    public function loadMore(): void
    {
        if ($this->page * $this->perPage >= $this->total) {
            return;
        }

        $this->page++;
        $this->loadPage(page: $this->page, perPage: $this->perPage);
    }

    protected function loadPage(int $page, int $perPage): void
    {
        if (! $this->modelType || ! $this->modelId) {
            return;
        }

        $paginator = resolve_static($this->modelType, 'query')
            ->whereKey($this->modelId)
            ->firstOrFail()
            ->activitiesAsSubject()
            ->with('causer:id,name')
            ->latest('id')
            ->paginate(perPage: $perPage, page: $page);

        $this->total = $paginator->total();

        $this->activities = $paginator->getCollection()
            ->map(fn (Activity $item) => $this->mapActivity($item))
            ->all();
    }

    protected function mapActivity(Activity $item): array
    {
        $itemArray = $item->toArray();
        $itemArray['causer']['name'] = $item->causer?->getLabel() ?: __('Unknown');
        $itemArray['causer']['avatar_url'] = $item->causer?->getAvatarUrl() ?: Icon::make('user')->getUrl();
        $itemArray['event'] = __($item->event);
        $itemArray['created_at_formatted'] = $item->created_at?->locale(app()->getLocale())->isoFormat('L LT');

        $changes = auth()->user() instanceof User
            ? ($item->attribute_changes?->toArray() ?? [])
            : ['old' => [], 'attributes' => []];

        foreach (['old', 'attributes'] as $changeKey) {
            if (! array_key_exists($changeKey, $changes)) {
                continue;
            }

            $changes[$changeKey] = collect($changes[$changeKey])
                ->mapWithKeys(fn (mixed $value, string $key) => [__(Str::headline($key)) => $value])
                ->toArray();
        }

        $itemArray['properties'] = $changes;

        return $itemArray;
    }
}
