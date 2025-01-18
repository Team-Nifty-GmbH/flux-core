<?php

namespace FluxErp\Traits;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

trait Widgetable
{
    public static function getLabel(): string
    {
        if (app()->runningInConsole()) {
            return Str::headline(class_basename(static::class));
        }

        return __(Str::headline(class_basename(static::class)));
    }

    public function placeholder(): View
    {
        if (method_exists(parent::class, 'placeholder')) {
            return parent::placeholder();
        }

        return view('flux::livewire.placeholders.box');
    }

    public function showTitle(): bool
    {
        return true;
    }
}
