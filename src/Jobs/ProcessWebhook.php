<?php

namespace FluxErp\Jobs;

use Carbon\Carbon;
use FluxErp\Models\User;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ProcessWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 1;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 1;

    private string $url;

    private string $signingKey;

    private object $model;

    private string $event;

    private User $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $url, string $signingKey, object $event, User $user)
    {
        $this->url = $url;
        $this->signingKey = $signingKey;
        $this->model = $event->model;
        $this->user = $user->withoutRelations();

        $classReflection = new \ReflectionClass(get_class($event));
        $this->event = $classReflection->getShortName();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (empty($url)) {
            return;
        }

        $timestamp = Carbon::now()->unix();
        $token = Str::random(50);

        $classReflection = new \ReflectionClass(get_class($this->model));

        $body = [
            'signature' => [
                'timestamp' => $timestamp,
                'token' => $token,
                'signature' => hash_hmac('sha256', $timestamp . $token, $this->signingKey),
            ],
            'model' => [
                $this->model->toArray(),
                'model_type' => $classReflection->getShortName(),
            ],
            'event' => [
                'event' => $this->event,
                'user' => $this->user->toArray(),
            ],
        ];

        $client = app(Client::class);
        $client->post($this->url, [
            'body' => json_encode($body),
        ]);
    }
}
