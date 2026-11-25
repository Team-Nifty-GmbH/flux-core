<?php

namespace FluxErp\Actions\MailAccount;

use ErrorException;
use FluxErp\Actions\FluxAction;
use FluxErp\Models\MailAccount;
use FluxErp\Rulesets\MailAccount\DiscoverMailAccountSettingsRuleset;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use SimpleXMLElement;
use Throwable;

class DiscoverMailAccountSettings extends FluxAction
{
    protected const int MAX_RESPONSE_BYTES = 1024 * 1024;

    protected const array SOCKET_TYPES = [
        'SSL' => 'ssl',
        'STARTTLS' => 'tls',
    ];

    protected static bool $hasPermission = false;

    protected float $deadline = 0;

    protected int $deadlineSeconds = 8;

    public static function models(): array
    {
        return [MailAccount::class];
    }

    protected function getRulesets(): string|array
    {
        return DiscoverMailAccountSettingsRuleset::class;
    }

    public function performAction(): ?array
    {
        $email = $this->getData('email');
        $domain = Str::lower(Str::afterLast($email, '@'));
        $query = '?emailaddress=' . urlencode($email);
        $this->deadline = microtime(true) + $this->deadlineSeconds;

        $sources = [
            fn () => $this->fromAutoconfig(
                'https://autoconfig.' . $domain . '/mail/config-v1.1.xml' . $query,
                $email
            ),
            fn () => $this->fromAutoconfig(
                'https://' . $domain . '/.well-known/autoconfig/mail/config-v1.1.xml' . $query,
                $email
            ),
            fn () => $this->fromAutoconfig('https://autoconfig.thunderbird.net/v1.1/' . $domain, $email),
            fn () => $this->fromMxIspdb($domain, $email),
            fn () => $this->fromSrv($domain),
        ];

        foreach ($sources as $source) {
            if ($settings = $source()) {
                return $settings;
            }
        }

        return null;
    }

    protected function dnsRecords(string $host, int $type): array
    {
        try {
            return dns_get_record($host, $type) ?: [];
        } catch (ErrorException) {
            return [];
        }
    }

    protected function fromAutoconfig(string $url, string $email): ?array
    {
        if (microtime(true) >= $this->deadline || ! $this->isPublicHost(parse_url($url, PHP_URL_HOST))) {
            return null;
        }

        try {
            $response = Http::timeout(min(5, max(1, (int) ceil($this->deadline - microtime(true)))))
                ->withOptions([
                    'allow_redirects' => false,
                    'on_headers' => function (ResponseInterface $response): void {
                        if ((int) $response->getHeaderLine('Content-Length') > static::MAX_RESPONSE_BYTES) {
                            throw new RuntimeException('Autoconfig response too large');
                        }
                    },
                ])
                ->get($url);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful() || strlen($response->body()) > static::MAX_RESPONSE_BYTES) {
            return null;
        }

        $previous = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($response->body(), options: LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $xml instanceof SimpleXMLElement) {
            return null;
        }

        $settings = [];

        if ($imap = data_get($xml->xpath('//incomingServer[@type="imap"]'), 0)) {
            $settings['host'] = (string) $imap->hostname;
            $settings['port'] = (int) $imap->port;
            $settings['encryption'] = static::SOCKET_TYPES[(string) $imap->socketType] ?? null;
        }

        if ($smtp = data_get($xml->xpath('//outgoingServer[@type="smtp"]'), 0)) {
            $settings['smtp_host'] = (string) $smtp->hostname;
            $settings['smtp_port'] = (int) $smtp->port;
            $settings['smtp_encryption'] = static::SOCKET_TYPES[(string) $smtp->socketType] ?? null;

            if ($username = (string) $smtp->username) {
                $settings['smtp_user'] = strtr($username, [
                    '%EMAILADDRESS%' => $email,
                    '%EMAILLOCALPART%' => Str::beforeLast($email, '@'),
                    '%EMAILDOMAIN%' => Str::afterLast($email, '@'),
                ]);
            }
        }

        if (! $settings) {
            return null;
        }

        $settings['source'] = parse_url($url, PHP_URL_HOST);

        return $settings;
    }

    protected function fromMxIspdb(string $domain, string $email): ?array
    {
        $mx = collect($this->dnsRecords($domain, DNS_MX))
            ->sortBy('pri')
            ->value('target');

        if (! $mx) {
            return null;
        }

        $baseDomain = implode('.', array_slice(explode('.', Str::lower(rtrim($mx, '.'))), -2));

        return $baseDomain === $domain
            ? null
            : $this->fromAutoconfig('https://autoconfig.thunderbird.net/v1.1/' . $baseDomain, $email);
    }

    protected function fromSrv(string $domain): ?array
    {
        $services = [
            ['_imaps', 'ssl', ['host', 'port', 'encryption']],
            ['_imap', 'tls', ['host', 'port', 'encryption']],
            ['_submissions', 'ssl', ['smtp_host', 'smtp_port', 'smtp_encryption']],
            ['_submission', 'tls', ['smtp_host', 'smtp_port', 'smtp_encryption']],
        ];

        $settings = [];
        foreach ($services as [$service, $encryption, $keys]) {
            if (array_key_exists($keys[0], $settings)) {
                continue;
            }

            $record = collect($this->dnsRecords($service . '._tcp.' . $domain, DNS_SRV))
                ->reject(fn (array $record) => rtrim(data_get($record, 'target', ''), '.') === '')
                ->sortBy('pri')
                ->first();

            if ($record) {
                $settings += array_combine(
                    $keys,
                    [
                        rtrim($record['target'], '.'),
                        (int) $record['port'],
                        $encryption,
                    ]
                );
            }
        }

        return $settings
            ? Arr::add($settings, 'source', 'DNS SRV')
            : null;
    }

    protected function isPublicHost(?string $host): bool
    {
        if (! $host) {
            return false;
        }

        $addresses = filter_var($host, FILTER_VALIDATE_IP)
            ? [$host]
            : array_merge(
                array_column($this->dnsRecords($host, DNS_A), 'ip'),
                array_column($this->dnsRecords($host, DNS_AAAA), 'ipv6')
            );

        return $addresses && array_all(
            $addresses,
            fn (string $address) => filter_var(
                $address,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            ) !== false
        );
    }
}
