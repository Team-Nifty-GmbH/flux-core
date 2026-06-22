<?php

namespace FluxErp\Models;

use FluxErp\Enums\ToastType;
use FluxErp\Support\TallstackUI\Interactions\Toast;
use FluxErp\Traits\Model\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Livewire\Component;

class Notification extends DatabaseNotification
{
    use MassPrunable, ResolvesRelationsThroughContainer;

    // Public methods
    public function menuArea(): ?string
    {
        return Str::before($this->menuRoute() ?? '', '.') ?: null;
    }

    public function menuRoute(): ?string
    {
        if (data_get($this->data, 'menu_indicator') === false) {
            return null;
        }

        return data_get($this->data, 'accept.route') ?: null;
    }

    public function prunable(): Builder
    {
        return static::query()
            ->where(
                static::getCreatedAtColumn(),
                '<',
                now()->subDays(30)
            )
            ->whereNotNull('read_at');
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

        $description = strip_tags(data_get($this->data, 'description', ''), '<br>');

        if (! is_null(data_get($this->data, 'progress'))) {
            $meta = strip_tags((string) data_get($this->data, 'progressMeta', ''));
            $metaHtml = $meta !== ''
                ? '<div class="text-xs opacity-70 mt-1">' . $meta . '</div>'
                : '';

            $description = <<<blade
                $description
                <div class="flex gap-1.5 items-center h-6">
                    <div class="overflow-hidden h-2 text-xs flex rounded bg-gray-200 dark:bg-gray-700 w-full">
                        <div
                            x-data="{
                                w: (window._toastProgress?.[toast.id] ?? 0),
                            }"
                            x-init="requestAnimationFrame(() => requestAnimationFrame(() => {
                                w = toast.progress * 100;
                                (window._toastProgress = window._toastProgress || {})[toast.id] = w;
                            }))"
                            x-bind:style="`width: \${w}%; transition: width 700ms ease-out;`"
                            class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500 dark:bg-indigo-700"
                        ></div>
                    </div>
                </div>
                $metaHtml
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

        if (! is_null(data_get($this->data, 'progress'))) {
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
