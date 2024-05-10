## Preqrequisites

We recommend to always extend the original class to avoid any issues with future updates.

For example if you want to customize the User model, you should extend the original User model and add an alias in your AppServiceProvider.

```php
// App\Models\User.php
class User extends \FluxErp\Models\User
{
    // Your customizations
}
```

```php
// AppServiceProvider.php
public function register(): void
{
    $this->app->bind(\FluxErp\Models\User::class, \App\Models\User::class);
}
```

This ensures that methods that are called on the User model are also present on your customized model.

## Models
To customize the models extend the model you want to customize and add an alias in your AppServiceProvider.

```php
// AppServiceProvider.php
public function register(): void
{
    $this->app->bind(\FluxErp\Models\User::class, \App\Models\User::class);
}
```

This packages enforces MorphMaps, therefore you have to add your customized models to the morph map.
Or you can add the `HasParentMorphClass` trait to your model.

```php
// App\Models\User.php
class User extends \FluxErp\Models\User
{
    use \FluxErp\Traits\HasParentMorphClass;
}
```

## Actions
All Flux actions are resolved via the app container, therefore you can simply add an alias in your AppServiceProvider, or do other shenanigans whilst the action class is getting resolved.

```php
// AppServiceProvider.php
public function register(): void
{
    $this->app->bind(\FluxErp\Actions\CreateUser::class, \App\Actions\CreateUser::class);
}
```

## Validation
Sometimes you need to add or change validation rules. The rules are placed in the Ruleset classes. You can extend the original ruleset and add your own rules.

```php
// App\Rulesets\UserRuleset.php
class CreateUserRuleset extends \FluxErp\Rulesets\UserRuleset
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'custom_field' => ['required', 'string'],
        ]);
    }
}
```

```php
// AppServiceProvider.php
public function register(): void
{
    $this->app->bind(\FluxErp\Rulesets\CreateUserRuleset::class, \App\Rulesets\CreateUserRuleset::class);
}
```


## Adding your own relationships

You can add your own relationships to the models by usign laravels `resolveRelationUsing` method.
If you only want to add a new relationship to a flux model you can do so in your AppServiceProvider.

```php
// App\Providers\AppServiceProvider.php

public function register(): void
{
    \FluxErp\Models\User::resolveRelationUsing('customRelation', function ($model) {
        return $model->hasOne(\App\Models\CustomModel::class);
    });
}
```

## Customizing states

To customize the states of a model you can register your own state config in your AppServiceProvider.
For more information about the state machine see [spatie/laravel-model-states](https://spatie.be/docs/laravel-model-states/v2/working-with-states/01-configuring-states#content-manually-registering-states)

```php
// App\Providers\AppServiceProvider.php

public function register(): void
{
    \FluxErp\States\State::registerStateConfig(
        \FluxErp\States\State::config()
            ->default(App\States\MyNewState::class)
            ->allowedTransition(App\States\MyNewState::class, \FluxErp\States\Order\Open::class)
            ->registerState(App\States\MyNewState::class),
        \FluxErp\States\Order\OrderState::class // You should set that if you crate the config from the base state
    )
}
```

If you just want to extend the existing state you can set the static property `$config`.

```php
// App\Providers\AppServiceProvider.php

public function register(): void
{
    \FluxErp\States\State::registerStateConfig(
        \FluxErp\States\OrderState::config()
            ->allowedTransition(App\States\MyNewState::class, \FluxErp\States\Order\Open::class)
            ->registerState(App\States\MyNewState::class)
    );
}
```


