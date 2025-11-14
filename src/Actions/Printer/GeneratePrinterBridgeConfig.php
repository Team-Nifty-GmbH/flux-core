<?php

namespace FluxErp\Actions\Printer;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Printer;
use FluxErp\Rulesets\Printer\GeneratePrinterBridgeConfigRuleset;
use Illuminate\Support\Facades\URL;

class GeneratePrinterBridgeConfig extends FluxAction
{
    public static function models(): array
    {
        return [Printer::class];
    }

    protected function getRulesets(): string|array
    {
        return GeneratePrinterBridgeConfigRuleset::class;
    }

    public function performAction(): array
    {
        $appUrl = config('app.url');
        $reverbAppId = config('reverb.app_id', config('broadcasting.connections.reverb.app_id'));
        $reverbAppKey = config('reverb.app_key', config('broadcasting.connections.reverb.key'));
        $reverbAppSecret = config('reverb.app_secret', config('broadcasting.connections.reverb.secret'));
        $reverbHost = config('reverb.host', config('broadcasting.connections.reverb.host'));
        $reverbPort = config('reverb.port', config('broadcasting.connections.reverb.port', 8080));
        $reverbScheme = config('reverb.scheme', config('broadcasting.connections.reverb.scheme', 'http'));
        $reverbUseTls = in_array($reverbScheme, ['https', 'wss']);

        // Generate API token for the authenticated user if not provided
        $apiToken = $this->data['api_token'] ?? null;

        // Build reverb auth endpoint
        $reverbAuthEndpoint = $appUrl ? rtrim($appUrl, '/') . '/broadcasting/auth' : null;

        // Build configuration array
        $config = [
            'instance_name' => $this->data['instance_name'] ?? 'default-instance',
            'printer_check_interval' => $this->data['printer_check_interval'] ?? 5,
            'job_check_interval' => $this->data['job_check_interval'] ?? 2,
            'flux_url' => $appUrl,
            'flux_api_token' => $apiToken,
            'api_port' => $this->data['api_port'] ?? 8080,
            'reverb_disabled' => $this->data['reverb_disabled'] ?? ! $reverbAppId,
            'reverb_app_id' => $reverbAppId,
            'reverb_app_key' => $reverbAppKey,
            'reverb_app_secret' => $reverbAppSecret,
            'reverb_use_tls' => $reverbUseTls,
            'reverb_host' => $reverbHost,
            'reverb_auth_endpoint' => $reverbAuthEndpoint,
        ];

        return $config;
    }
}