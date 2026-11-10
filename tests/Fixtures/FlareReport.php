<?php

namespace Spatie\FlareClient;

use Throwable;

// Client v3 ships an empty Report.php. This stands in for the v1 Report that
// laravel-ignition hands to CensorPersonalData, with the same merge behavior.
if (! class_exists(Report::class)) {
    class Report
    {
        public ?string $message = null;

        public ?Throwable $throwable = null;

        public array $userProvidedContext = [];

        public function __construct(private readonly array $context) {}

        public function allContext(): array
        {
            return array_replace_recursive($this->context, $this->userProvidedContext);
        }

        public function getMessage(): ?string
        {
            return $this->message;
        }

        public function getThrowable(): ?Throwable
        {
            return $this->throwable;
        }

        public function message(string $message): static
        {
            $this->message = $message;

            return $this;
        }

        public function throwable(Throwable $throwable): static
        {
            $this->throwable = $throwable;

            return $this;
        }

        public function userProvidedContext(array $userProvidedContext): static
        {
            $this->userProvidedContext = $userProvidedContext;

            return $this;
        }
    }
}
