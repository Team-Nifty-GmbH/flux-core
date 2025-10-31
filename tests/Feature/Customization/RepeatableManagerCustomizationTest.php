<?php

use FluxErp\Console\Scheduling\RepeatableManager;

test('custom repeatable manager is resolved from container', function (): void {
    $customManager = new class() extends RepeatableManager
    {
        public function getIdentifier(): string
        {
            return 'custom-repeatable-manager';
        }

        public function isCustom(): bool
        {
            return true;
        }
    };

    $this->app->singleton(RepeatableManager::class, get_class($customManager));

    $manager = $this->app->make(RepeatableManager::class);

    expect($manager)->toBeInstanceOf(get_class($customManager));
    expect(get_class($manager))->not->toBe(RepeatableManager::class);
    expect($manager->isCustom())->toBeTrue();
    expect($manager->getIdentifier())->toBe('custom-repeatable-manager');
});

test('custom repeatable manager persists across resolves', function (): void {
    $customManager = new class() extends RepeatableManager
    {
        public function isCustom(): bool
        {
            return true;
        }
    };

    $this->app->singleton(RepeatableManager::class, get_class($customManager));

    $manager1 = $this->app->make(RepeatableManager::class);
    $manager2 = $this->app->make(RepeatableManager::class);

    expect($manager1)->toBe($manager2);
    expect($manager1)->toBeInstanceOf(get_class($customManager));
});
