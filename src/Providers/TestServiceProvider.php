<?php

namespace FluxErp\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\ServiceProvider;
use Livewire\Features\SupportTesting\Testable;
use PHPUnit\Framework\Assert;
use function Livewire\invade;

class TestServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! $this->app->runningUnitTests()) {
            return;
        }

        $this->registerTestMacros();
    }

    protected function registerTestMacros(): void
    {
        if (! Testable::hasMacro('assertExecutesJs')) {
            Testable::macro(
                'assertExecutesJs',
                function (string $js) {
                    Assert::assertStringContainsString(
                        $js,
                        implode(' ', data_get($this->lastState->getEffects(), 'xjs.*.expression', []))
                    );

                    return $this;
                }
            );
        }

        if (! Testable::hasMacro('assertToastNotification')) {
            Testable::macro(
                'assertToastNotification',
                function (
                    ?string $title = null,
                    ?string $type = null,
                    ?string $description = null,
                    ?bool $expandable = null,
                    ?int $timeout = null,
                    ?bool $persistent = null,
                    string|int|null $id = null
                ) {
                    $this->assertDispatched(
                        'tallstackui:toast',
                        function (
                            string $eventName,
                            array $params
                        ) use ($title, $type, $description, $expandable, $timeout, $persistent, $id) {
                            return array_key_exists('component', $params)
                                && (is_null($type) || data_get($params, 'type') === $type)
                                && (is_null($title) || data_get($params, 'title') === $title)
                                && (is_null($description) || data_get($params, 'description') === $description)
                                && (is_null($expandable) || data_get($params, 'expandable') === $expandable)
                                && (is_null($timeout) || data_get($params, 'timeout') === $timeout)
                                && (is_null($persistent) || data_get($params, 'persistent') === $persistent)
                                && (is_null($id) || data_get($params, 'persistent') === $id);
                        }
                    );

                    return $this;
                }
            );
        }

        if (! Testable::hasMacro('cycleTabs')) {
            Testable::macro(
                'cycleTabs',
                function (string $tabPropertyName = 'tab'): void {
                    $tabs = $this->instance()->getTabs();

                    foreach ($tabs as $tab) {
                        $this
                            ->set($tabPropertyName, $tab->component)
                            ->assertStatus(200);

                        if ($tab->isLivewireComponent) {
                            $this->assertSeeLivewire($tab->component);
                        }
                    }

                    $this->set($tabPropertyName, $tabs[0]->component);
                }
            );
        }

        if (! Testable::hasMacro('datatableCreate')) {
            Testable::macro(
                'datatableCreate',
                function (string $formPropertyName, array $formValues = [], ?string $modalName = null): Testable {
                    if (
                        is_null($modalName) &&
                        in_array(
                            \FluxErp\Traits\Livewire\SupportsAutoRender::class,
                            class_uses_recursive($this->instance()->{$formPropertyName})
                        )
                    ) {
                        $modalName = $this->instance()->{$formPropertyName}->modalName();
                    }

                    $this->assertStatus(200)
                        ->call('edit')
                        ->assertExecutesJs('$modalOpen(\'' . $modalName . '\')');

                    foreach ($formValues as $propertyName => $propertyValue) {
                        $this->set($formPropertyName . '.' . $propertyName, $propertyValue);
                    }

                    $this->call('save')
                        ->assertStatus(200)
                        ->assertHasNoErrors()
                        ->assertReturned(true);

                    return $this;
                }
            );
        }

        if (! Testable::hasMacro('datatableDelete')) {
            Testable::macro(
                'datatableDelete',
                function (Model $model, TestCase $testCase): Testable {
                    $this->assertStatus(200)
                        ->call('loadData')
                        ->assertCount('data.data', 1)
                        ->assertSet('data.data.0.id', $model->getKey())
                        ->call('delete', $model->getKey())
                        ->assertHasNoErrors()
                        ->assertStatus(200)
                        ->assertCount('data.data', 0);

                    if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                        invade($testCase)->assertSoftDeleted($model);
                    } else {
                        invade($testCase)->assertDatabaseMissing($model);
                    }

                    return $this;
                }
            );
        }

        if (! Testable::hasMacro('datatableEdit')) {
            Testable::macro(
                'datatableEdit',
                function (Model $model, string $routeName): Testable {
                    $this->assertStatus(200)
                        ->call('loadData')
                        ->assertCount('data.data', 1)
                        ->assertSet('data.data.0.id', $model->getKey())
                        ->call('edit', $model->getKey())
                        ->assertHasNoErrors()
                        ->assertStatus(200)
                        ->assertRedirectToRoute($routeName, $model->getKey());

                    return $this;
                }
            );
        }
    }
}
