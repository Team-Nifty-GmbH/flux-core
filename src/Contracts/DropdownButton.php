<?php

namespace FluxErp\Contracts;

interface DropdownButton extends EditorButton
{
    public function dropdownContent(): array;

    public function dropdownRef(): string;
}
