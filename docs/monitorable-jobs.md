## Make a job monitorable

To make a job monitorable, you need to add the `isMonitored` trait to the job class and implement the `ShouldBeMonitored` interface.

```php
// App\Jobs\CreateUser.php

namespace App\Jobs;

use FluxErp\Contracts\ShouldBeMonitored;

class CreateUser extends Job implements ShouldBeMonitored
{
    use \FluxErp\Traits\IsMonitored;

    public function handle(): void
    {
        // Do something

        for ($i = 0; $i < 100; $i++) {
            $this->queueProgress($i);
            $this->message('Creating user ' . $i + 1);

            // if you want to update multiple times in one loop, you can use the update method
            $this->queueUpdate([
                'progress' => $i,
                'message' => 'Creating user ' . $i + 1
            ]);
        }

        $this->message('All users created');
        $this->accept(
            NotificationAction::make()
                ->text('View users')
                ->route('users.index')
        );
    }
}
```

### Updating the progress of a job

You can send updates to the frontend by calling the `queueProgress` method on the job instance.

```php
$this->queueProgress(50);
```

This will update the notification message and the progress bar in the frontend.

### Sending messages to the frontend

To add a message to the frontend, you can call the `message` method on the job instance.

```php
$this->message('User created');
```

### Adding a button to the frontend

You can add a button to the frontend by calling the `accept` or `reject` method on the job instance.
The methods both accept a `NotificationAction` instance as a parameter.

```php
use FluxErp\NotificationAction;

$this->accept(NotificationAction::make()->text('Accept')->route('users.index'));
```

You can also add js code to the button by calling the `execute` method on the `NotificationAction` instance.

```php
$this->accept(
    NotificationAction::make()
        ->text('Accept')
        ->execute(<<<'JS'
            alert("Hello World")
        JS);
```

## Monitorable Batches

To make a batch monitorable, you have to call the `monitoredBatch` method on the `Bus` facade.

```php
use Illuminate\Support\Facades\Bus;

Bus::monitoredBatch($jobs)->name('Create users')->dispatch();
```

Please be aware that all jobs inside a batch will not be monitored if the batch is not monitorable.
The mentioned methods above will not be reflected on the frontend notification for batches.
