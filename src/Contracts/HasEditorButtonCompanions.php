<?php

namespace FluxErp\Contracts;

interface HasEditorButtonCompanions
{
    /**
     * @return list<array{class: class-string<\Livewire\Component>, params?: array<string, mixed>}>
     */
    public function companions(): array;
}
