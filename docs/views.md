## Blade files

Blade files are the files that are used to render the HTML output of a view. They are stored in the `resources/views` directory. Blade files use the `.blade.php` file extension and are compiled into plain PHP code and cached in the `storage/framework/views` directory.

To customize a blade file you can create a new file with the same relative location in your projects `resources/views` directory. For example, to customize the `resources/views/layouts/app.blade.php` file you would create a new file at `resources/views/layouts/app.blade.php`.

You should prefer to use the `@extendFlux` directive to extend a blade file. This will allow you to override the content of the blade file without having to copy the entire file. For example, to override the content of the `resources/views/livewire/order/order.blade.php` file you would create a new file at `resources/views/livewire/order/order.blade.php` with the following content:

```php
@section('actions')
    <livewire:custom-order-action :order="$order" />
@endsection
@extendFlux('livewire.order.order')
```

If you want to extend the section rather than replace it you can use the `@parent` directive in your section.

```php
@section('actions')
    @parent
    <livewire:custom-order-action :order="$order" />
@endsection
@extendFlux('livewire.order.order')
```

> [!notice]
> The `@extendFlux` directive should always be the last line in your blade file.

## Livewire components

If you want to replace a whole livewire component you can create a news component with the same name in your project.
To do so just use the default artisan command:

```bash
php artisan make:livewire Order/Order
```

This will create a new livewire component at `app/Http/Livewire/Order/Order.php` and a blade file at `resources/views/livewire/order/order.blade.php`.

If you want to extend the functionality you can extend the original component and override the methods you want to change.

```php
<?php

namespace App\Http\Livewire\Order;

use FluxErp\Livewire\Order\Order as BaseOrder;

class Order extends BaseOrder
{
    public function myNewFunction()
    {
        // Do something
    }
}
```

> [!notice]
> If you override the render method you should add the `@extendFlux` directive to the end of your blade file.

## Tabs

You can register your own tabs to every livewire component that uses the `WithTabs` trait.
To do so you can use the `registerTab` method in the `boot` method of your `AppServiceProvider`.

```php

use FluxErp\Livewire\Contact\Contact;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Event::listen(
            'tabs.rendering: ' . Contact::class,
            function (Contact $component) {
                $component->mergeTabsToRender([
                    // The component name follows the same pattern as the livewire component name
                    // If your Livewire component is used like this: <livewire:contact.custom-tab />
                    // The component name would be contact.custom-tab
                    \FluxErp\Htmlables\TabButton::make('contact.custom-tab')
                        ->text(__('Custom Tab'))
                        ->icon('icon')
                        ->isLivewireComponent()
                        ->wireModel('contact.id')
                ]);
            }
        );
    }
}
```
