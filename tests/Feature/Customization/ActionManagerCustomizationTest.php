<?php

use FluxErp\Actions\ActionManager;

test('custom action manager is resolved from container', function (): void {
    $customManager = new class() extends ActionManager
    {
        public function getIdentifier(): string
        {
            return 'custom-action-manager';
        }

        public function isCustom(): bool
        {
            return true;
        }
    };

    // Simulate what a user would do in their AppServiceProvider
    $this->app->singleton(ActionManager::class, get_class($customManager));

    // After application boot, check if the custom manager is in the container
    $manager = $this->app->make(ActionManager::class);

    // Assert it's our custom manager, not the default one
    expect($manager)->toBeInstanceOf(get_class($customManager));
    expect(get_class($manager))->not->toBe(ActionManager::class);
    expect($manager->isCustom())->toBeTrue();
    expect($manager->getIdentifier())->toBe('custom-action-manager');
});

test('custom action manager persists across resolves', function (): void {
    $customManager = new class() extends ActionManager
    {
        public function isCustom(): bool
        {
            return true;
        }
    };

    $this->app->singleton(ActionManager::class, get_class($customManager));

    // Resolve multiple times
    $manager1 = $this->app->make(ActionManager::class);
    $manager2 = $this->app->make(ActionManager::class);

    // Assert same instance (singleton behavior)
    expect($manager1)->toBe($manager2);
    expect($manager1)->toBeInstanceOf(get_class($customManager));
});
