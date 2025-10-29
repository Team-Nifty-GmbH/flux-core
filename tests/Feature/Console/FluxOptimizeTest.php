<?php

use FluxErp\Actions\ActionManager;
use FluxErp\Actions\Product\CreateProduct;
use FluxErp\Console\Scheduling\Repeatable;
use FluxErp\Console\Scheduling\RepeatableManager;
use FluxErp\Facades\Action;
use FluxErp\Facades\Widget;
use FluxErp\Jobs\ProcessSubscriptionOrderJob;
use FluxErp\Widgets\WidgetManager;

beforeEach(function (): void {
    $cacheFiles = [
        'flux-actions.php',
        'flux-widgets.php',
        'flux-repeatables.php',
        'flux-view-classes.php',
        'flux-livewire-components.php',
        'flux-blade-components.php',
    ];

    foreach ($cacheFiles as $file) {
        $path = app()->bootstrapPath('cache/' . $file);
        if (file_exists($path)) {
            unlink($path);
        }
    }
});

test('flux:optimize generates all cache files', function (): void {
    $this->artisan('flux:optimize')
        ->assertExitCode(0);

    $cacheFiles = [
        'flux-actions.php',
        'flux-widgets.php',
        'flux-repeatables.php',
        'flux-view-classes.php',
        'flux-livewire-components.php',
        'flux-blade-components.php',
    ];

    foreach ($cacheFiles as $file) {
        $path = app()->bootstrapPath('cache/' . $file);
        expect(file_exists($path))
            ->toBeTrue('Cache file ' . $file . ' should exist');
    }
});

test('flux:optimize generates valid actions cache', function (): void {
    $this->artisan('flux:optimize')
        ->assertExitCode(0);

    $cachePath = app()->bootstrapPath('cache/flux-actions.php');
    $actions = require $cachePath;

    expect($actions)->toBeArray();

    $hasActions = false;
    foreach ($actions as $actionList) {
        if (! empty($actionList)) {
            $hasActions = true;
            expect($actionList)->toBeArray();

            break;
        }
    }

    expect($hasActions)->toBeTrue('Actions cache should contain at least one action');

    $foundCreateProduct = false;
    foreach ($actions as $actionList) {
        if (in_array(CreateProduct::class, $actionList)) {
            $foundCreateProduct = true;

            break;
        }
    }

    expect($foundCreateProduct)->toBeTrue('Should contain CreateProduct action');
});

test('flux:optimize generates valid widgets cache', function (): void {
    $this->artisan('flux:optimize')
        ->assertExitCode(0);

    $cachePath = app()->bootstrapPath('cache/flux-widgets.php');
    $widgets = require $cachePath;

    expect($widgets)->toBeArray();

    foreach ($widgets as $cacheKey => $widgetList) {
        expect($widgetList)->toBeArray();
    }
});

test('flux:optimize generates valid repeatables cache', function (): void {
    $this->artisan('flux:optimize')
        ->assertExitCode(0);

    $cachePath = app()->bootstrapPath('cache/flux-repeatables.php');
    $repeatables = require $cachePath;

    expect($repeatables)->toBeArray();

    $foundRepeatables = false;
    foreach ($repeatables as $cacheKey => $repeatableList) {
        if (! empty($repeatableList)) {
            $foundRepeatables = true;

            foreach ($repeatableList as $name => $class) {
                expect($name)->toBeString()
                    ->and($class)->toBeString()
                    ->and(class_exists($class))->toBeTrue()
                    ->and(is_a($class, Repeatable::class, true))->toBeTrue();
            }
        }
    }

    expect($foundRepeatables)->toBeTrue('Should find at least one repeatable');

    $foundKnownRepeatable = false;
    foreach ($repeatables as $repeatableList) {
        if (in_array(ProcessSubscriptionOrderJob::class, $repeatableList)) {
            $foundKnownRepeatable = true;

            break;
        }
    }

    expect($foundKnownRepeatable)->toBeTrue('Should contain ProcessSubscriptionOrderJob');
});

test('flux:optimize generates valid livewire components cache', function (): void {
    $this->artisan('flux:optimize')
        ->assertExitCode(0);

    $cachePath = app()->bootstrapPath('cache/flux-livewire-components.php');
    $components = require $cachePath;

    expect($components)->toBeArray();

    foreach ($components as $alias => $class) {
        expect($alias)->toBeString()
            ->and($class)->toBeString()
            ->and(class_exists($class))->toBeTrue();
    }

    expect(count($components))->toBeGreaterThan(0);
});

test('flux:optimize generates valid blade components cache', function (): void {
    $this->artisan('flux:optimize')
        ->assertExitCode(0);

    $cachePath = app()->bootstrapPath('cache/flux-blade-components.php');
    $components = require $cachePath;

    expect($components)->toBeArray();

    foreach ($components as $component) {
        expect($component)->toBeArray()
            ->and($component)->toHaveKeys(['view', 'alias'])
            ->and($component['view'])->toBeString()
            ->and($component['alias'])->toBeString();
    }
});

test('flux:optimize cache files contain expected structure', function (): void {
    $this->artisan('flux:optimize')
        ->assertExitCode(0);

    $actionsCache = require app()->bootstrapPath('cache/flux-actions.php');
    expect($actionsCache)->toBeArray();
    foreach ($actionsCache as $cacheKey => $actions) {
        expect(is_string($cacheKey))->toBeTrue()
            ->and($actions)->toBeArray();
    }

    $widgetsCache = require app()->bootstrapPath('cache/flux-widgets.php');
    expect($widgetsCache)->toBeArray();
    foreach ($widgetsCache as $cacheKey => $widgets) {
        expect(is_string($cacheKey))->toBeTrue()
            ->and($widgets)->toBeArray();
    }

    $repeatablesCache = require app()->bootstrapPath('cache/flux-repeatables.php');
    expect($repeatablesCache)->toBeArray();
    foreach ($repeatablesCache as $cacheKey => $repeatables) {
        expect(is_string($cacheKey))->toBeTrue()
            ->and($repeatables)->toBeArray();
    }
});

test('action manager loads actions from cache', function (): void {
    $this->artisan('flux:optimize')
        ->assertExitCode(0);

    $this->refreshApplication();

    $actionManager = app(ActionManager::class);
    $actions = $actionManager->all();

    expect($actions)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and($actions->count())->toBeGreaterThan(0);

    $foundCreateProduct = false;
    foreach ($actions as $action) {
        if ($action['class'] === CreateProduct::class) {
            $foundCreateProduct = true;
            expect($action)->toHaveKey('name')
                ->and($action)->toHaveKey('class');
            break;
        }
    }

    expect($foundCreateProduct)->toBeTrue('ActionManager should load CreateProduct from cache')
        ->and(Action::all())->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and(Action::all()->count())->toBe($actions->count());
});

test('widget manager loads widgets from cache', function (): void {
    $this->artisan('flux:optimize')
        ->assertExitCode(0);

    $this->refreshApplication();

    $widgetManager = app(WidgetManager::class);
    $widgets = $widgetManager->all();

    expect($widgets)->toBeArray()
        ->and(Widget::all())->toBeArray()
        ->and(count(Widget::all()))->toBe(count($widgets));

    foreach ($widgets as $widget) {
        expect($widget)->toBeArray()
            ->and($widget)->toHaveKeys(['component_name', 'label', 'class', 'dashboard_component']);
    }
});

test('repeatable manager loads repeatables from cache', function (): void {
    $this->artisan('flux:optimize')
        ->assertExitCode(0);

    $this->refreshApplication();

    $repeatableManager = app(RepeatableManager::class);
    $repeatables = $repeatableManager->all();

    expect($repeatables)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and($repeatables->count())->toBeGreaterThan(0);

    $foundProcessSubscriptionOrder = false;
    foreach ($repeatables as $repeatable) {
        if ($repeatable['class'] === ProcessSubscriptionOrderJob::class) {
            $foundProcessSubscriptionOrder = true;
            expect($repeatable)->toHaveKey('name')
                ->and($repeatable)->toHaveKey('class');

            break;
        }
    }

    expect($foundProcessSubscriptionOrder)
        ->toBeTrue('RepeatableManager should load ProcessSubscriptionOrderJob from cache');
});

test('managers use cache when available and not in console', function (): void {
    $this->artisan('flux:optimize')
        ->assertExitCode(0);

    app()->offsetUnset('env');
    putenv('APP_ENV=testing');

    $this->refreshApplication();

    expect(file_exists(app()->bootstrapPath('cache/flux-actions.php')))->toBeTrue()
        ->and(file_exists(app()->bootstrapPath('cache/flux-widgets.php')))->toBeTrue()
        ->and(file_exists(app()->bootstrapPath('cache/flux-repeatables.php')))->toBeTrue();

    $actionManager = app(ActionManager::class);
    $widgetManager = app(WidgetManager::class);
    $repeatableManager = app(RepeatableManager::class);

    expect(count($actionManager->all()))->toBeGreaterThan(0)
        ->and($widgetManager->all())->toBeArray()
        ->and(count($repeatableManager->all()))->toBeGreaterThan(0);
});
