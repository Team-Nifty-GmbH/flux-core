# Preparing the class

if you want to make a class printable you have to implement the `OffersPrinting` interface and use the `Printable` trait.

```php
<?php

namespace App\Models;

use FluxErp\Contracts\OffersPrinting;
use FluxErp\Traits\Printable;

class Order extends Model implements OffersPrinting
{
    use Printable;

    ...

    public function getPrintViews(): array
    {
        return [
            'my-printable-view' => \App\View\Printing\MyPrintableView::class,
            'my-other-printable-view' => fn (Order $order => $order->is_locked
                ? \App\View\Printing\MyOtherPrintableView::class
                : null
        ];
    }
}
```

## Printable model collections

If you wish to print a list of a specific model you should create a new Collection class for that model.

```php
<?php

namespace App\Collections;

use FluxErp\Contracts\OffersPrinting;
use Illuminate\Database\Eloquent\Collection;

class OrderCollection extends Collection implements OffersPrinting
{
    use Printable;

    public function getPrintViews(): array
    {
        return [
            'order-list' => \App\View\Printing\OrderList::class,
        ];
    }
}
```

You need to tell your model that this class should be used as the collection class.

```php

use App\Collections\OrderCollection;

class Order extends Model implements OffersPrinting
{
    use Printable;

    ...

    public function newCollection(array $models = []): OrderCollection
    {
        return new OrderCollection($models);
    }

    ...
}
```

# Creating a new Printable View

You can add your own Print views or override the default ones by running the following command:

```bash
php artisan make:printable-view
```

This will create a new class file in the `app/Views/Printing` directory and a blade file in the `resources/views/printing` directory.

The class file will look like this:

```php
<?php

namespace App\View\Printing;

use FluxErp\View\Printing\PrintableView;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;

class MyPrintableView extends PrintableView
{
    public function __construct(public \FluxErp\Models\Order $order)
    {
        //
    }

    public function render(): View
    {
        return view('printing.my-printable-view');
    }

    public function getFileName(): string
    {
        return $this->getSubject();
    }

    public function getSubject(): string
    {
        // TODO: Implement getSubject() method
    }

    public function getModel(): ?Model
    {
        return $this->order;
    }
}
```

Printable views are basically view components that can be rendered as PDFs.
They should always expect a class that implements the OfferPrinting interface and uses the Printable trait.

The `getSubject` method should return the title of the PDF.
For example the subject for an Invoice would look like this:

```php
public function getSubject(): string
{
    return 'Invoice ' . $this->invoice->number;
}
```

## the beforePrinting and afterPrinting Methods

You can use the `beforePrinting` and `afterPrinting` methods to add some logic before and after the PDF is rendered.

For example if you want to lock the order before creating the invoice:

```php

class Invoice extends PrintableView
{
    ...

    public function beforePrinting(): void
    {
        if ($this->preview || $this->model->invoice_number) {
            return;
        }

        $this->model->getSerialNumber('invoice_number');
        $this->model->invoice_date = now();
        $this->model->is_locked = true;

        if ($this->model->state instanceof Draft) {
            $this->model->state->transitionTo('open');
        }

        $this->model->save();
    }
    ...
```

## Shared data

The result of your views `getSubject` will be available in all blade files as `$subject`.
If your printable class has an `client` property or relation you can access it in the blade file like this:

```blade
<div>
    <h1>{{ $client->name }}</h1>
    <h2>{{ $subject }}</h2>
</div>
```

# Registering the view

You have to register your view in your service provider.
You can do this by adding the following code to the `boot` method of your service provider:

```php
public function register(): void
{
    \FluxErp\Models\Order::registerPrintView('my-printable-view', \App\View\Printing\MyPrintableView::class);
}
```

If you want to override the default view just use the same name as the default view.

## Conditional views

You can register the view as conditional.
To do so use a closure as the second parameter of the `registerPrintView` method.
The closure should return the view class or null if the view should not be available.

The first parameter of the closure is always an instance of the class that implements the OfferPrinting interface.

For example if you want to make the view only available if the order is locked you can do it like this:

```php
public function register(): void
{
    \FluxErp\Models\Order::registerPrintView('my-printable-view', \App\View\Printing\MyPrintableView::class, function (\FluxErp\Models\Order $order) {
        return $order->isLocked();
    });
}
```

# Editing the blade file

The blade file will look like this:

```blade
<div>
    <!-- An unexamined life is not worth living. - Socrates -->
</div>
```

You can use tailwind to design your PDFs.
Keep in mind that Javascript is not supported in PDFs, but you can still use it when you want to render your PrintableView as HTML.
