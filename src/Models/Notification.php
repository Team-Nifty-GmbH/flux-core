<?php

namespace FluxErp\Models;

use FluxErp\Enums\ToastType;
use FluxErp\Support\TallstackUI\Interactions\Toast;
use FluxErp\Traits\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Component;

class Notification extends DatabaseNotification
{
    use MassPrunable, ResolvesRelationsThroughContainer;

    public function prunable(): mixed
    {
        return static::where('created_at', '<', now()->subDays(30))->whereNotNull('read_at');
    }

    public function toast(?Component $component = null): Toast
    {
        $title = data_get($this->data, 'title', '');
        if ($this->created_at->isToday()) {
            $time = $this->created_at
                ->timezone(auth()->user()?->timezone ?? config('app.timezone'))
                ->toTimeString('minute');
        } else {
            $time = $this->created_at->diffForHumans();
        }

        $title = <<<blade
            <div class="flex justify-between gap-1.5">
                <span class="font-semibold overflow-hidden whitespace-nowrap text-ellipsis">$title</span>
                <span class="text-xs whitespace-nowrap">$time</span>
            </div>
        blade;

        $description = strip_tags(data_get($this->data, 'description', ''));

        if (data_get($this->data, 'progress')) {
            $description = <<<blade
                $description
                <div class="flex gap-1.5 items-center h-6">
                    <div class="overflow-hidden h-2 text-xs flex rounded bg-gray-200 dark:bg-gray-700 w-full">
                        <div x-bind:style="{width: toast.progress * 100 + '%'}" class="transition-all shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500 dark:bg-indigo-700"></div>
                    </div>
                </div>
            blade;
        }

        /** @var Toast $toast */
        $toast = Toast::make($component)
            ->{data_get($this->data, 'toastType') ?? ToastType::INFO->value}($title, $description);

        if (data_get($this->data, 'accept')) {
            $toast->confirm(
                data_get($this->data, 'accept.label', __('Accept')),
                data_get($this->data, 'accept.method', 'acceptNotify'),
                data_get($this->data, 'accept.params', $this->getKey())
            );
        }

        if (data_get($this->data, 'reject')) {
            $toast->cancel(
                data_get($this->data, 'reject.label', __('Reject')),
                data_get($this->data, 'reject.method', 'rejectNotify'),
                data_get($this->data, 'reject.params', $this->getKey())
            );
        }

        if (data_get($this->data, 'progress')) {
            $toast->progress(data_get($this->data, 'progress'));
        }

        if (data_get($this->data, 'accept') || data_get($this->data, 'persistent')) {
            $toast->persistent();
        }

        if (data_get($this->data, 'toastId')) {
            $toast->setEventName('toast-upsert');
        }

        if (strlen(data_get($this->data, 'description')) > 100 && ! data_get($this->data, 'progress')) {
            $toast->expandable();
        }

        return $toast;
    }
}
