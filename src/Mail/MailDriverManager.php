<?php

namespace FluxErp\Mail;

use FluxErp\Contracts\MailSyncDriver;
use Illuminate\Support\Manager;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

class MailDriverManager extends Manager
{
    /**
     * Driver names that share the IMAP-family configuration shape
     * (host, port, encryption, password). External drivers (e.g. msgraph)
     * are not part of this set.
     *
     * @return array<int, string>
     */
    public function imapFamilyDriverNames(): array
    {
        return ['imap', 'pop3', 'nntp'];
    }

    public function getDefaultDriver(): string
    {
        return 'imap';
    }

    /**
     * @return array<int, string>
     */
    public function driverNames(): array
    {
        $builtIn = [];

        foreach ((new ReflectionClass(static::class))->getMethods(ReflectionMethod::IS_PROTECTED) as $method) {
            if (! Str::startsWith($method->getName(), 'create') || ! Str::endsWith($method->getName(), 'Driver')) {
                continue;
            }

            $builtIn[] = Str::snake(Str::substr($method->getName(), 6, -6), '-');
        }

        return array_values(array_unique(array_merge($builtIn, array_keys($this->customCreators))));
    }

    protected function createImapDriver(): MailSyncDriver
    {
        return $this->container->make(ImapMailSyncDriver::class);
    }

    protected function createPop3Driver(): MailSyncDriver
    {
        return $this->container->make(ImapMailSyncDriver::class);
    }

    protected function createNntpDriver(): MailSyncDriver
    {
        return $this->container->make(ImapMailSyncDriver::class);
    }
}
