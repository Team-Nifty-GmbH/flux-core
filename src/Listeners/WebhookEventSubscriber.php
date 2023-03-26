<?php

namespace FluxErp\Listeners;

use FluxErp\Jobs\ProcessWebhook;
use FluxErp\Models\Setting;

class WebhookEventSubscriber
{
    /**
     * Handle incoming events.
     */
    public function sendWebhook($event): void
    {
        $setting = Setting::query()
            ->where('key', 'webhooks')
            ->first();

        if (empty($setting)) {
            return;
        }

        $webhooks = collect($setting->settings->webhooks);
        $jobs = $webhooks
            ->where('event', get_class($event))
            ->all();

        foreach ($jobs as $job) {
            ProcessWebhook::dispatch($job->url, $setting->settings->signing_key, $event, auth()->user());
        }
    }

    /**
     * Register the listeners for the subscriber.
     * E.g. CommentCreated::class => 'sendWebhook'
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events): array
    {
        return [

        ];
    }
}
