<?php

namespace FluxErp\Contracts;

interface ActionInterface
{
    public static function make(array $data): static;

    public static function name(): string;

    public static function description(): string|null;

    public static function models(): array;

    public function execute();

    public function validate(): static;
}
