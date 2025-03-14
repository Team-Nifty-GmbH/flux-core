You can add your own widgets that are available on the dashboard.

To add your widget you can use this artisan command:

```bash
php artisan make:widget MyWidget
```

A widget is basically a Livewire component that implements the `UserWidget` interface.

```php
<?php

namespace App\Http\Livewire\Widgets;

use FluxErp\Contracts\UserWidget;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;

class MyWidget extends Component implements UserWidget
{
    public function render(): View|Factory
    {
        return view('livewire.widgets.my_widget');
    }

    public static function getLabel(): string
    {
        return Str::headline(class_basename(self::class));
    }
}
```

The `getLabel` method is used to display the widget in the widget list.

## Registering widgets

If you keep your widgets in the default location and namespace they will be automatically registered.
If you choose to put them somewhere else you can register them in your ServiceProvider:

```php
<?php

namespace App\Providers;

use FluxErp\Contracts\UserWidget;
use FluxErp\Facades\Widget;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register all widgets in the path
        Widget::autoDiscoverWidgets(app_path('Http/Widgets'), 'App\Http\Widgets');

        // Register a single widget
        Widget::registerWidget(MyWidget::class);
    }
}
```

> [!warning]
> A widget can not accept any mount parameters.
> If you need to pass data to your widget you can use the `session` or `cache` facade.
> When using auto discovery, only valid widgets will be registered.
> Auto discovery will skip any widget that does not implement the `UserWidget` interface or expects a mount parameter.

## Permissions

Every registered widget will automaticially get a permission assigned to it when you run the
`php artisan init:permissions` command.

The permission will be named `widgets.widget.{widget-name}`. For example: `widgets.widget.my-widget`.

You can use this permission to control who can see the widget on the dashboard.
