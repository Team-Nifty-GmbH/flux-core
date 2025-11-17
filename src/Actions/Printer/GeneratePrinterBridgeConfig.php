<?php

namespace FluxErp\Actions\Printer;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Token\CreateToken;
use FluxErp\Models\Printer;
use FluxErp\Models\Token;
use FluxErp\Rulesets\Printer\GeneratePrinterBridgeConfigRuleset;

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

        $instanceName = $this->data['instance_name'] ?? 'default-instance';
        $forceRegenerate = $this->data['force_regenerate'] ?? false;

        if ($forceRegenerate) {
            $updated = Token::query()
                ->where('name', $instanceName)
                ->whereNull('expires_at')
                ->update(['expires_at' => now()]);

            \Log::info("Expired {$updated} token(s) for instance: {$instanceName}");
        }

        $token = CreateToken::make([
            'name' => $instanceName,
            'description' => 'API token for printer bridge instance: '.$instanceName,
            'abilities' => ['*'],
        ])
            ->checkPermission()
            ->validate()
            ->execute();

        $apiToken = $token->plain_text_token;

        $reverbAuthEndpoint = $appUrl ? rtrim($appUrl, '/').'/broadcasting/auth' : null;

        $config = [
            'instance_name' => $instanceName,
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
