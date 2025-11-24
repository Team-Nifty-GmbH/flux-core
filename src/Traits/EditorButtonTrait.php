<?php

namespace FluxErp\Traits;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Illuminate\View\ComponentAttributeBag;

trait EditorButtonTrait
{
    public static function identifier(): string
    {
        return Str::kebab(class_basename(static::class));
    }

    public static function scopes(): array
    {
        return [];
    }

    public function render(): string
    {
        $attributes = new ComponentAttributeBag([
            'flat' => true,
            'color' => 'secondary',
        ]);

        if ($command = $this->command()) {
            $attributes = $attributes->merge(['x-on:click' => $command]);
        }

        if ($isActive = $this->isActive()) {
            $attributes = $attributes->merge([
                'x-bind:class' => "{ 'bg-primary-100 dark:bg-primary-900': editorState >= 0 && ($isActive) }",
            ]);
        }

        if ($icon = $this->icon()) {
            $attributes = $attributes->merge(['icon' => $icon]);
        }

        if ($text = $this->text()) {
            $attributes = $attributes->merge(['text' => $text]);
        }

        if ($displayTitle = $this->title() ?? ($this->tooltip() ? __($this->tooltip()) : null)) {
            $attributes = $attributes->merge(['title' => $displayTitle]);
        }

        if (method_exists($this, 'attributes')) {
            $attributes = $attributes->merge($this->attributes());
        }

        return Blade::render(<<<'Blade'
            <x-button {{ $attributes }} />
        Blade, ['attributes' => $attributes]);
    }

    public function command(): ?string
    {
        $mark = lcfirst(class_basename(static::class));
        $toggleMethod = 'toggle' . class_basename(static::class);

        return <<<JS
            editor().chain().focus().$toggleMethod().run()
            JS;
    }

    public function isActive(): ?string
    {
        $mark = lcfirst(class_basename(static::class));

        return <<<JS
            editor().isActive('$mark')
            JS;
    }

    public function icon(): ?string
    {
        return null;
    }

    public function text(): ?string
    {
        return null;
    }

    public function title(): ?string
    {
        return null;
    }

    public function tooltip(): ?string
    {
        return null;
    }
}
