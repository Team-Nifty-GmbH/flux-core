<?php

namespace FluxErp\Contracts;

use FluxErp\View\Components\Editor;
use Illuminate\Support\Stringable;

interface EditorButton
{
    public static function identifier(): Stringable;

    public static function scopes(): array;

    public function render(): string;

    public function command(): ?string;

    public function isActive(): ?string;

    public function icon(): ?string;

    public function text(): ?string;

    public function title(): ?string;

    public function tooltip(): ?string;

    public function setEditor(Editor $editor): static;
}
