<?php

namespace FluxErp\Console\Commands;

use FluxErp\Facades\Action;
use FluxErp\Facades\Repeatable;
use FluxErp\Traits\HasDefault;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class FluxOptimize extends Command
{
    protected $description = 'Warms the cache for the Flux application';

    protected bool $forget = false;

    protected $signature = 'flux:optimize';

    public function handle(): void
    {
        $this->optimizeDefaultModels();
        $this->optimizeModelInfo();

        if ($this->forget) {
            $this->optimizeActions();
            $this->optimizeViewClasses();
            $this->optimizeRepeatable();
        }
    }

    protected function optimizeActions(): void
    {
        foreach (Action::getDiscoveries() as $cacheKey => $actions) {
            Cache::forget($cacheKey);
        }
    }

    protected function optimizeDefaultModels(): void
    {
        foreach (Relation::morphMap() as $alias => $model) {
            if (! in_array(HasDefault::class, class_uses_recursive($model))) {
                continue;
            }

            /** @var Model $model */
            $this->forget ? Cache::forget('default_' . $alias) : resolve_static($model::class, 'default');
        }
    }

    protected function optimizeModelInfo(): void
    {
        if ($this->forget) {
            Cache::forget(config('tall-datatables.cache_key') . '.modelInfo');

            return;
        }

        // warm the cache for all models
        model_info_all();
    }

    protected function optimizeRepeatable(): void
    {
        foreach (Repeatable::getDiscoveries() as $cacheKey => $repeatables) {
            Cache::forget($cacheKey);
        }
    }

    protected function optimizeViewClasses(): void
    {
        Cache::forget('flux.view_classes.' . Str::slug('FluxErp\\Livewire\\'));
    }
}
