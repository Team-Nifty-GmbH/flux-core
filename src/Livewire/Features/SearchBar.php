<?php

namespace FluxErp\Livewire\Features;

use Exception;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Scout\Searchable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\WithPagination;

class SearchBar extends Component
{
    use Actions, WithPagination;

    public ?array $load = null;

    public bool $mobile = false;

    public array $modelLabels = [];

    public array $return = [];

    public string $search = '';

    public array|string $searchModel = '';

    public bool $show = false;

    public function mount(): void
    {
        if ($this->searchModel === '') {
            $this->searchModel = collect(Relation::morphMap())
                ->map(fn (string $model) => resolve_static($model, 'class'))
                ->filter(fn (string $class) => in_array(
                    Searchable::class,
                    class_uses_recursive($class)
                )
                    && method_exists($class, 'detailRoute')
                    && method_exists($class, 'getLabel')
                )
                ->values()
                ->toArray();
        }

        foreach ((array) $this->searchModel as $searchModel) {
            $this->modelLabels[$searchModel] = [
                'label' => __(Str::of(morph_alias($searchModel))->plural()->headline()->toString()),
                'icon' => method_exists($searchModel, 'icon') ? $searchModel::icon()->getSvg() : null,
            ];
        }
    }

    public function render(): View|Factory|Application
    {
        return view(
            $this->mobile
                ? 'flux::livewire.features.search-bar-mobile'
                : 'flux::livewire.features.search-bar',
            ['results' => $this->return]
        );
    }

    #[Renderless]
    public function showDetail(string $model, int $id): void
    {
        /** @var Model $model */
        $modelInstance = resolve_static($model, 'query')->whereKey($id)->first();

        if (! $modelInstance) {
            $this->toast()
                ->error(__('Record not found'))
                ->send();

            return;
        }

        if ($this->mobile) {
            $this->modalClose('search-bar-mobile-modal');
        } else {
            $this->js(<<<'JS'
                showDropdown = false;
            JS);
        }

        $this->redirect($modelInstance->detailRoute(), true);
    }

    public function updatedSearch(): void
    {
        if ($this->search) {
            if (is_array($this->searchModel)) {
                $return = [];
                foreach ($this->searchModel as $model) {
                    try {
                        $result = resolve_static($model, 'search', ['query' => $this->search])
                            ->toEloquentBuilder()
                            ->latest()
                            ->limit(5)
                            ->get()
                            ->filter(fn ($item) => $item->detailRoute())
                            ->map(fn (Model $item) => [
                                'id' => $item->getKey(),
                                'label' => $item->getLabel(),
                                'src' => method_exists($item, 'getAvatarUrl') ? $item->getAvatarUrl() : null,
                            ]);

                        if (count($result)) {
                            $return[$model] = collect($result)->toArray();
                        }
                    } catch (Exception) {
                        // ignore
                    }

                    if (count($return) >= 10) {
                        break;
                    }
                }

                $this->return = $return;
            } else {
                $result = resolve_static($this->searchModel, 'search', ['query' => $this->search])
                    ->paginate();

                if ($this->load && $result && $result instanceof LengthAwarePaginator) {
                    $result->load($this->load);
                }

                $this->return = count($result->items()) ? $result->items() : null;
                $this->show = true;
            }
        } else {
            $this->return = [];
        }

        $this->skipRender();
    }
}
