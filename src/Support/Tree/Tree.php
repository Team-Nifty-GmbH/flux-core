<?php

namespace FluxErp\Support\Tree;

use Closure;
use Exception;
use FluxErp\Traits\Makeable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Wireable;

class Tree implements Wireable
{
    use Makeable;

    public function __construct(
        protected array $tree = [],
        protected string $key = 'id',
        protected string $parentKey = 'parent_id',
        protected string $childKey = 'children'
    ) {}

    public static function fromLivewire($value)
    {
        return resolve_static(static::class, 'make', [$value]);
    }

    public function toLivewire(): array
    {
        return $this->getTree();
    }

    public function getTree(): array
    {
        return $this->tree;
    }

    public function setTree(array $tree): static
    {
        $this->tree = $tree;

        return $this;
    }

    public function mapTree(Closure $callback): static
    {
        $this->setTree($this->mapNodes($this->getTree(), $callback));

        return $this;
    }

    public function getNode(array|int|string|null $subject): ?array
    {
        if (is_array($subject)) {
            $subject = data_get($subject, $this->key);
        }

        if (is_null($subject)) {
            return null;
        }

        return data_get($this->tree, $this->traverseTree($subject, $this->tree));
    }

    /**
     * @throws Exception
     */
    public function addOrUpdateNode(array $subject): array
    {
        $index = null;
        if ($key = data_get($subject, $this->key)) {
            $index = $this->traverseTree($key, $this->tree);
        }

        if (is_null($index)) {
            $parentKey = data_get($subject, $this->parentKey);
            if ($parentKey) {
                $parentIndex = $this->traverseTree($parentKey, $this->tree);

                if (is_null($parentIndex)) {
                    throw new Exception('Parent node not found');
                }

                $parentNode = data_get($this->tree, $parentIndex);
                $parentNode[$this->childKey][] = array_merge(
                    $subject,
                    [
                        $this->key => $key = Str::uuid()->toString(),
                        $this->parentKey => data_get($parentNode, $this->key),
                        $this->childKey => [],
                    ]
                );

                data_set($this->tree, $parentIndex, $parentNode);
            } else {
                $this->tree[] = array_merge(
                    $subject,
                    [
                        $this->key => $key = Str::uuid()->toString(),
                        $this->parentKey => null,
                        $this->childKey => [],
                    ]
                );
            }
        } else {
            data_set(
                $this->tree,
                $index,
                array_merge(
                    data_get($this->tree, $index),
                    array_diff_key(
                        $subject,
                        array_flip([$this->key, $this->parentKey, $this->childKey])
                    )
                )
            );
        }

        return $this->getNode($key);
    }

    /**
     * @throws Exception
     */
    public function moveNode(array|int|string|null $subject, array|int|string|null $target): static
    {
        if (is_array($subject)) {
            $subject = data_get($subject, $this->key);
        }

        if (is_null($subject) || is_null($subjectIndex = $this->traverseTree($subject, $this->tree))) {
            throw new Exception('Subject node not found');
        }

        if (is_array($target)) {
            $target = data_get($target, $this->key);
        }

        $subjectNode = data_get($this->tree, $subjectIndex);
        if (is_null($target) || is_null($targetIndex = $this->traverseTree($target, $this->tree))) {
            $this->tree[] = array_merge($subjectNode, [$this->parentKey => null]);
        } else {
            $targetNode = data_get($this->tree, $targetIndex);
            $targetNode[$this->childKey][] = array_merge(
                $subjectNode,
                [
                    $this->parentKey => data_get($targetNode, $this->key),
                ]
            );

            data_set($this->tree, $targetIndex, $targetNode);
        }

        Arr::forget($this->tree, $subjectIndex);

        return $this;
    }

    public function removeNode(array|int|string|null $subject): static
    {
        if (is_array($subject)) {
            $subject = data_get($subject, $this->key);
        }

        if (is_null($subject)) {
            return $this;
        }

        $index = $this->traverseTree($subject, $this->tree);
        if (is_null($index)) {
            return $this;
        }

        Arr::forget($this->tree, $index);

        return $this;
    }

    protected function traverseTree(
        int|string $key,
        array $children,
        int|string|null $currentLevel = null
    ): int|string|null {
        foreach ($children as $i => $child) {
            if (is_null($currentLevel)) {
                $level = $i;
            } else {
                $level = $currentLevel . '.' . $this->childKey . '.' . $i;
            }

            if (data_get($child, $this->key) === $key) {
                return $level;
            } elseif (data_get($child, $this->childKey)) {
                $index = $this->traverseTree($key, data_get($child, $this->childKey), $level);

                if (! is_null($index)) {
                    return $index;
                }
            }
        }

        return null;
    }

    protected function mapNodes(array $nodes, Closure $callback): array
    {
        $mapped = [];
        foreach ($nodes as $key => $node) {
            $children = Arr::pull($node, $this->childKey);
            $mapped[$key] = $callback($node);
            $mapped[$key][$this->childKey] = $this->mapNodes($children, $callback);
        }

        return $mapped;
    }
}
