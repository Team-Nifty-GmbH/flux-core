<?php

namespace FluxErp\Livewire\Features;

use FluxErp\Traits\Scout\Searchable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use WireUi\Traits\Actions;

class SearchBar extends Component
{
    use Actions, WithPagination;

    public string $search = '';

    public array|string $searchModel = '';

    public ?string $searchResultComponent;

    public string $onClick = '';

    public bool $show = false;

    public array $return = [];

    public array $modelLabels = [];

    public ?array $load = null;

    public function mount(): void
    {
        if ($this->searchModel === '') {
            $this->searchModel = model_info_all()
                ->filter(fn ($model) => in_array(Searchable::class, $model->traits->toArray()))
                ->map(fn ($model) => $model->class)
                ->toArray();
        }

        foreach ((array) $this->searchModel as $searchModel) {
            $this->modelLabels[$searchModel] = [
                'label' => __(Str::plural(class_basename($searchModel))),
                'icon' => method_exists($searchModel, 'icon') ? $searchModel::icon()->getSvg() : null,
            ];
        }
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.features.search-bar', ['results' => $this->return]);
    }

    public function updatedSearch(): void
    {
        if ($this->search) {
            if (is_array($this->searchModel)) {
                $return = [];
                foreach ($this->searchModel as $model) {
                    try {
                        $result = app($model)->search($this->search)
                            ->toEloquentBuilder()
                            ->limit(5)
                            ->get()
                            ->filter(fn ($item) => $item->detailRoute())
                            ->map(fn (Model $item) => [
                                'id' => $item->getKey(),
                                'label' => method_exists($item, 'getLabel') ?
                                    $item->getLabel() : $item->getAttribute('name'),
                                'src' => method_exists($item, 'getAvatarUrl') ? $item->getAvatarUrl() : null,
                            ]);

                        if (count($result)) {
                            $return[$model] = collect($result)->toArray();
                        }
                    } catch (\Exception $e) {
                        // ignore
                    }

                    if (count($return) >= 10) {
                        break;
                    }
                }

                $this->return = $return;
            } else {
                $result = app($this->searchModel)->search($this->search)->paginate();

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

    public function showDetail(string $model, int $id): void
    {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $modelInstance = app($model)->query()->whereKey($id)->first();

        if (! $modelInstance) {
            $this->notification()->error(__('Record not found'));

            return;
        }

        $this->redirect($modelInstance->detailRoute(), true);
    }
}
