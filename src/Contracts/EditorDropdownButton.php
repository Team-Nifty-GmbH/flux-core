<?php

namespace FluxErp\Contracts;

interface EditorDropdownButton extends EditorButton
{
    public function dropdownContent(): array;

    public function dropdownRef(): string;
}
