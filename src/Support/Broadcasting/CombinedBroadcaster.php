<?php

namespace FluxErp\Support\Broadcasting;

use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Illuminate\Broadcasting\Broadcasters\MercureBroadcaster;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

/**
 * Sends every event to several broadcast connections at once, e.g. Mercure for the
 * browser and Reverb for clients that only speak the Pusher protocol.
 */
class CombinedBroadcaster extends Broadcaster
{
    /**
     * @param  array<int, Broadcaster>  $broadcasters
     */
    public function __construct(protected array $broadcasters) {}

    public function auth($request)
    {
        return $this->broadcasterFor($request)->auth($request);
    }

    public function broadcast(array $channels, $event, array $payload = []): void
    {
        $failures = [];

        foreach ($this->broadcasters as $broadcaster) {
            try {
                $broadcaster->broadcast($channels, $event, $payload);
            } catch (Throwable $e) {
                $failures[] = $e;
            }
        }

        $exception = count($failures) === count($this->broadcasters) ? array_shift($failures) : null;

        array_map(report(...), $failures);

        if ($exception) {
            throw $exception;
        }
    }

    /**
     * @return array<int, Broadcaster>
     */
    public function broadcasters(): array
    {
        return $this->broadcasters;
    }

    public function channel($channel, $callback, $options = []): static
    {
        foreach ($this->broadcasters as $broadcaster) {
            $broadcaster->channel($channel, $callback, $options);
        }

        return $this;
    }

    public function validAuthenticationResponse($request, $result)
    {
        return $this->broadcasterFor($request)
            ->validAuthenticationResponse($request, $result);
    }

    /**
     * Mercure authorizes a list of channels in one request, the Pusher protocol one channel per request.
     */
    protected function broadcasterFor(Request $request): Broadcaster
    {
        $wantsMercure = $request->has('channel_names');

        return array_find(
            $this->broadcasters,
            fn (Broadcaster $broadcaster) => $broadcaster instanceof MercureBroadcaster === $wantsMercure
        ) ?? throw new AccessDeniedHttpException();
    }
}
