<?php

namespace FluxErp\Actions\Printer;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Token\CreateToken;
use FluxErp\Models\Permission;
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
        $instanceName = $this->getData('instance_name');

        if ($this->getData('force_regenerate')) {
            resolve_static(Token::class, 'query')
                ->where('name', $instanceName)
                ->whereNull('expires_at')
                ->update(['expires_at' => now()]);
        }

        $requiredPermissionNames = [
            'api.printers.get',
            'api.printers.create',
            'api.printers.update',
            'api.printers.delete',
            'api.print-jobs.get',
            'api.print-jobs.create',
            'api.print-jobs.update',
        ];

        $permissionIds = Permission::query()
            ->whereIn('name', $requiredPermissionNames)
            ->where('guard_name', 'token')
            ->pluck('id')
            ->toArray();

        $token = CreateToken::make([
            'name' => $instanceName,
            'description' => 'API token for printer bridge instance: ' . $instanceName,
            'abilities' => ['*'],
            'permissions' => $permissionIds,
        ])
            ->checkPermission()
            ->validate()
            ->execute();

        $reverbScheme = config('reverb.scheme', config('broadcasting.connections.reverb.scheme', 'http'));

        return [
            'instance_name' => $instanceName,
            'printer_check_interval' => $this->getData('printer_check_interval'),
            'job_check_interval' => $this->getData('job_check_interval'),
            'flux_url' => config('app.url') ?? '',
            'flux_api_token' => $token->plain_text_token ?? '',
            'api_port' => $this->getData('api_port'),
            'reverb_disabled' => $this->getData('reverb_disabled'),
            'reverb_app_id' => config('reverb.app_id', config('broadcasting.connections.reverb.app_id')) ?? '',
            'reverb_app_key' => config('reverb.app_key', config('broadcasting.connections.reverb.key')) ?? '',
            'reverb_app_secret' => config('reverb.app_secret', config('broadcasting.connections.reverb.secret')) ?? '',
            'reverb_use_tls' => in_array($reverbScheme, ['https', 'wss']),
            'reverb_host' => config('reverb.host', config('broadcasting.connections.reverb.host')) ?? '',
            'reverb_auth_endpoint' => config('app.url') ? rtrim(config('app.url'), '/') . '/broadcasting/auth' : '',
        ];
    }
}
