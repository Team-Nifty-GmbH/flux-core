<?php

namespace FluxErp\Actions\Printer;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Media\DownloadMedia;
use FluxErp\Actions\PrintJob\UpdatePrintJob;
use FluxErp\Actions\Token\CreateToken;
use FluxErp\Models\Permission;
use FluxErp\Models\Printer;
use FluxErp\Models\Token;
use FluxErp\Rulesets\Printer\GeneratePrinterBridgeConfigRuleset;
use FluxErp\Support\Broadcasting\MercureHubConfig;
use Illuminate\Validation\ValidationException;

class GeneratePrinterBridgeConfig extends FluxAction
{
    public static function defaults(?bool $mercureEnabled = null): array
    {
        $mercureEnabled ??= resolve_static(MercureHubConfig::class, 'usesMercure');

        return [
            'instance_name' => 'default-instance',
            'printer_check_interval' => 5,
            'job_check_interval' => 2,
            'api_port' => 8080,
            'reverb_disabled' => $mercureEnabled
                || ! config('reverb.app_id', config('broadcasting.connections.reverb.app_id')),
            'mercure_enabled' => $mercureEnabled,
            'force_regenerate' => false,
        ];
    }

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
            'api.print-jobs.get',
            'api.print-jobs.{id}.get',
            'action.' . resolve_static(CreatePrinter::class, 'name'),
            'action.' . resolve_static(UpdatePrinter::class, 'name'),
            'action.' . resolve_static(DeletePrinter::class, 'name'),
            'action.' . resolve_static(UpdatePrintJob::class, 'name'),
            'action.' . resolve_static(DownloadMedia::class, 'name'),
        ];

        $permissionIds = resolve_static(Permission::class, 'query')
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
        $mercureHubUrl = config('broadcasting.connections.mercure.public_url') ?: null;

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
            'reverb_app_secret' => $this->getData('reverb_disabled')
                ? ''
                : config('reverb.app_secret', config('broadcasting.connections.reverb.secret')) ?? '',
            'reverb_use_tls' => in_array($reverbScheme, ['https', 'wss']),
            'reverb_host' => config('reverb.host', config('broadcasting.connections.reverb.host')) ?? '',
            'reverb_auth_endpoint' => config('app.url') ? rtrim(config('app.url'), '/') . '/broadcasting/auth' : '',
            'mercure_enabled' => $this->getData('mercure_enabled'),
            'mercure_hub_url' => $this->getData('mercure_enabled') ? $mercureHubUrl : null,
        ];
    }

    protected function prepareForValidation(): void
    {
        $mercureEnabled = $this->data['mercure_enabled'] ?? null;

        foreach (static::defaults(is_null($mercureEnabled) ? null : (bool) $mercureEnabled) as $key => $value) {
            $this->data[$key] ??= $value;
        }
    }

    protected function validateData(): void
    {
        parent::validateData();

        if ($this->getData('mercure_enabled') && ! resolve_static(MercureHubConfig::class, 'usesMercure')) {
            throw ValidationException::withMessages([
                'mercure_enabled' => [__('This instance does not broadcast over Mercure.')],
            ])
                ->errorBag('generatePrinterBridgeConfig');
        }
    }
}
