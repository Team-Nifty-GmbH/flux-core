<?php

namespace FluxErp\Livewire;

use FluxErp\Traits\Model\HasParentChildRelations;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;
#[Lazy]
class FamilyTree extends Component
{
    #[Locked]
    public string $modelType;

    #[Locked]
    public int $modelId;

    public ?int $maxDepth = null;

    public function render(): View
    {
        $tree = null;

        if (in_array(HasParentChildRelations::class, class_uses_recursive($this->modelType))) {
            $model = resolve_static($this->modelType, 'query')
                ->whereKey($this->modelId)
                ->firstOrFail();

            $rootId = $model->familyRootKey() ?? $this->modelId;

            $eagerLoad = $this->buildEagerLoadString();

            $root = $rootId === $model->getKey()
                ? $model->load($eagerLoad ? [$eagerLoad] : [])
                : resolve_static($this->modelType, 'query')
                    ->with($eagerLoad ? [$eagerLoad] : [])
                    ->whereKey($rootId)
                    ->first();

            $tree = blank($root) ? [] : $this->buildTree($root);
        }

        return view('flux::livewire.family-tree', ['tree' => $tree]);
    }

    public function placeholder(): View
    {
        return view('flux::livewire.placeholders.box');
    }

    protected function buildEagerLoadString(): string
    {
        if (! is_null($this->maxDepth) && $this->maxDepth <= 0) {
            return '';
        }

        $depth = $this->maxDepth ?? 10;

        return implode('.', array_fill(0, $depth, 'children'));
    }

    protected function buildTree(Model $node, int $depth = 0): array
    {
        $label = method_exists($node, 'getLabel')
            ? ($node->getLabel() ?? (string) $node->getKey())
            : (data_get($node, 'name') ?? (string) $node->getKey());

        return [
            'id' => $node->getKey(),
            'label' => $label,
            'url' => method_exists($node, 'getUrl') ? $node->getUrl() : null,
            'is_current' => $node->getKey() === $this->modelId,
            'children' => (! is_null($this->maxDepth) && $depth >= $this->maxDepth)
                ? []
                : $node->children
                    ->map(fn (Model $child) => $this->buildTree($child, $depth + 1))
                    ->toArray(),
        ];
    }
}
