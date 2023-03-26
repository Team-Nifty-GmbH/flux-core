<?php

return [
    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY', null),
        'index-settings' => [
            \FluxErp\Models\SerialNumber::class => [
                'filterableAttributes' => [
                    'address_id',
                ],
            ],
            \FluxErp\Models\Permission::class => [
                'filterableAttributes' => [
                    'guard_name',
                ],
                'sortableAttributes' => [
                    'name',
                ],
            ],
            \FluxErp\Models\Ticket::class => [
                'filterableAttributes' => [
                    'authenticatable_type',
                    'authenticatable_id',
                    'state',
                ],
                'sortableAttributes' => ['*'],
            ],
            \FluxErp\Models\Address::class => [
                'filterableAttributes' => [
                    'is_main_address',
                    'contact_id',
                ],
                'sortableAttributes' => ['*'],
            ],
            \FluxErp\Models\Order::class => [
                'filterableAttributes' => [
                    'parent_id',
                    'contact_id',
                    'is_locked',
                ],
                'sortableAttributes' => ['*'],
            ],
            \FluxErp\Models\Product::class => [],
            \FluxErp\Models\ProjectTask::class => [
                'filterableAttributes' => [
                    'project_id',
                    'state',
                ],
                'sortableAttributes' => ['*'],
            ],
            \FluxErp\Models\User::class => [
                'filterableAttributes' => [
                    'is_active',
                ],
            ],
            \FluxErp\Models\Warehouse::class => [],
        ],
    ],
];
