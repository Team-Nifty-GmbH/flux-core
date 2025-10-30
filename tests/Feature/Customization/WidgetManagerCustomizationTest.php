<?php

use FluxErp\Widgets\WidgetManager;

test('custom widget manager is resolved from container', function (): void {
    $customManager = new class() extends WidgetManager
    {
        public function getIdentifier(): string
        {
            return 'custom-widget-manager';
        }

        public function isCustom(): bool
        {
            return true;
        }
    };

    $this->app->singleton(WidgetManager::class, get_class($customManager));

    $manager = $this->app->make(WidgetManager::class);

    expect($manager)->toBeInstanceOf(get_class($customManager));
    expect(get_class($manager))->not->toBe(WidgetManager::class);
    expect($manager->isCustom())->toBeTrue();
    expect($manager->getIdentifier())->toBe('custom-widget-manager');
});

test('custom widget manager persists across resolves', function (): void {
    $customManager = new class() extends WidgetManager
    {
        public function isCustom(): bool
        {
            return true;
        }
    };

    $this->app->singleton(WidgetManager::class, get_class($customManager));

    $manager1 = $this->app->make(WidgetManager::class);
    $manager2 = $this->app->make(WidgetManager::class);

    expect($manager1)->toBe($manager2);
    expect($manager1)->toBeInstanceOf(get_class($customManager));
});
