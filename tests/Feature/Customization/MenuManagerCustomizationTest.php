<?php

use FluxErp\Menu\MenuManager;

test('custom menu manager is resolved from container', function (): void {
    $customManager = new class() extends MenuManager
    {
        public function getIdentifier(): string
        {
            return 'custom-menu-manager';
        }

        public function isCustom(): bool
        {
            return true;
        }
    };

    $this->app->singleton(MenuManager::class, get_class($customManager));

    $manager = $this->app->make(MenuManager::class);

    expect($manager)->toBeInstanceOf(get_class($customManager));
    expect(get_class($manager))->not->toBe(MenuManager::class);
    expect($manager->isCustom())->toBeTrue();
    expect($manager->getIdentifier())->toBe('custom-menu-manager');
});

test('custom menu manager persists across resolves', function (): void {
    $customManager = new class() extends MenuManager
    {
        public function isCustom(): bool
        {
            return true;
        }
    };

    $this->app->singleton(MenuManager::class, get_class($customManager));

    $manager1 = $this->app->make(MenuManager::class);
    $manager2 = $this->app->make(MenuManager::class);

    expect($manager1)->toBe($manager2);
    expect($manager1)->toBeInstanceOf(get_class($customManager));
});
