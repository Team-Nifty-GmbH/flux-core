<?php

use FluxErp\Support\Tree\Tree;

beforeEach(function (): void {
    $this->tree = Tree::make([
        [
            'id' => 'root-1',
            'parent_id' => null,
            'name' => 'Root 1',
            'children' => [
                [
                    'id' => 'child-1',
                    'parent_id' => 'root-1',
                    'name' => 'Child 1',
                    'children' => [],
                ],
                [
                    'id' => 'child-2',
                    'parent_id' => 'root-1',
                    'name' => 'Child 2',
                    'children' => [
                        [
                            'id' => 'grandchild-1',
                            'parent_id' => 'child-2',
                            'name' => 'Grandchild 1',
                            'children' => [],
                        ],
                    ],
                ],
            ],
        ],
        [
            'id' => 'root-2',
            'parent_id' => null,
            'name' => 'Root 2',
            'children' => [],
        ],
    ]);
});

test('getTree returns full tree', function (): void {
    expect($this->tree->getTree())->toHaveCount(2);
});

test('getNode finds root node', function (): void {
    $node = $this->tree->getNode('root-1');

    expect($node['name'])->toBe('Root 1');
});

test('getNode finds nested child', function (): void {
    $node = $this->tree->getNode('grandchild-1');

    expect($node['name'])->toBe('Grandchild 1');
});

test('getNode for non-existing returns full tree', function (): void {
    // traverseTree returns null, data_get($tree, null) returns the whole array
    expect($this->tree->getNode('nonexistent'))->toBeArray();
});

test('getNode with null returns null', function (): void {
    expect($this->tree->getNode(null))->toBeNull();
});

test('addOrUpdateNode adds root node', function (): void {
    $node = $this->tree->addOrUpdateNode(['name' => 'New Root']);

    expect($node['name'])->toBe('New Root');
    expect($node['parent_id'])->toBeNull();
    expect($this->tree->getTree())->toHaveCount(3);
});

test('addOrUpdateNode adds child to parent', function (): void {
    $node = $this->tree->addOrUpdateNode([
        'name' => 'New Child',
        'parent_id' => 'root-2',
    ]);

    expect($node['name'])->toBe('New Child');
    expect($node['parent_id'])->toBe('root-2');
});

test('addOrUpdateNode updates existing node', function (): void {
    $this->tree->addOrUpdateNode([
        'id' => 'child-1',
        'name' => 'Updated Child',
    ]);

    $node = $this->tree->getNode('child-1');

    expect($node['name'])->toBe('Updated Child');
});

test('addOrUpdateNode throws for missing parent', function (): void {
    expect(fn () => $this->tree->addOrUpdateNode([
        'name' => 'Orphan',
        'parent_id' => 'nonexistent-parent',
    ]))->toThrow(Exception::class, 'Parent node not found');
});

test('removeNode removes root', function (): void {
    $this->tree->removeNode('root-2');

    expect($this->tree->getTree())->toHaveCount(1);
});

test('removeNode removes nested child', function (): void {
    $this->tree->removeNode('grandchild-1');

    $child2 = $this->tree->getNode('child-2');

    expect($child2['children'])->toBeEmpty();
});

test('removeNode with null is noop', function (): void {
    $this->tree->removeNode(null);

    expect($this->tree->getTree())->toHaveCount(2);
});

test('removeNode with nonexistent is noop', function (): void {
    $this->tree->removeNode('nonexistent');

    expect($this->tree->getTree())->toHaveCount(2);
});

test('moveNode moves child to root', function (): void {
    $this->tree->moveNode('child-1', null);

    expect($this->tree->getTree())->toHaveCount(3);
});

test('moveNode throws for nonexistent subject', function (): void {
    expect(fn () => $this->tree->moveNode('nonexistent', 'root-2'))
        ->toThrow(Exception::class, 'Subject node not found');
});

test('mapTree transforms all nodes', function (): void {
    $this->tree->mapTree(fn (array $node) => array_merge($node, ['visited' => true]));

    $root = $this->tree->getNode('root-1');

    expect($root['visited'])->toBeTrue();
});

test('setTree replaces entire tree', function (): void {
    $this->tree->setTree([['id' => 'only', 'parent_id' => null, 'children' => []]]);

    expect($this->tree->getTree())->toHaveCount(1);
});

test('toLivewire returns tree array', function (): void {
    expect($this->tree->toLivewire())->toBe($this->tree->getTree());
});
