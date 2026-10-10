<?php

namespace FluxErp\Support\Mail;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use SimpleXMLElement;
use Throwable;

class MailServerDiscovery
{
    protected const array SOCKET_TYPES = [
        'SSL' => 'ssl',
        'STARTTLS' => 'tls',
    ];

    public function discover(string $email): ?array
    {
        $domain = Str::lower(Str::afterLast($email, '@'));
        $query = '?emailaddress=' . urlencode($email);

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
        return @dns_get_record($host, $type) ?: [];
    }

    protected function fromAutoconfig(string $url, string $email): ?array
    {
        try {
            $response = Http::timeout(5)->get($url);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
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

        // The last two labels as registrable domain; wrong for e.g. co.uk, which would need a public suffix list.
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
                $settings += array_combine($keys, [rtrim($record['target'], '.'), (int) $record['port'], $encryption]);
            }
        }

        return $settings
            ? Arr::add($settings, 'source', 'DNS SRV')
            : null;
    }
}
