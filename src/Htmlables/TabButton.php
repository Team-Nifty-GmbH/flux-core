<?php

namespace FluxErp\Htmlables;

use Closure;
use FluxErp\Models\Permission;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\ComponentAttributeBag;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TallStackUi\View\Components\Button\Button;
use Throwable;

class TabButton implements Htmlable
{
    protected bool $shouldRender = true;

    public function __construct(
        public string $component,
        public bool $round = false,
        public bool $square = false,
        public bool $outline = false,
        public bool $flat = true,
        public ?string $color = null,
        public ?string $size = null,
        public ?string $text = null,
        public ?string $icon = null,
        public ?string $position = null,
        public ?string $loading = null,
        public ?string $delay = null,
        public ?string $href = null,
        public bool $isLivewireComponent = false,
        public ?string $wireModel = null,
        public ?array $attributes = []
    ) {}

    public static function make(
        string $component,
        bool $round = false,
        bool $square = false,
        bool $outline = false,
        bool $flat = true,
        ?string $color = null,
        ?string $size = null,
        ?string $text = null,
        ?string $icon = null,
        ?string $position = null,
        ?string $loading = null,
        ?string $delay = null,
        ?string $href = null,
        bool $isLivewireComponent = false,
        ?string $wireModel = null,
        ?array $attributes = []
    ): static {
        return new static(
            component: $component,
            round: $round,
            square: $square,
            outline: $outline,
            flat: $flat,
            color: $color,
            size: $size,
            text: $text,
            icon: $icon,
            position: $position,
            loading: $loading,
            delay: $delay,
            href: $href,
            isLivewireComponent: $isLivewireComponent,
            wireModel: $wireModel,
            attributes: $attributes,
        );
    }

    public function attributes(array $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function color(?string $color = null): static
    {
        $this->color = $color;

        return $this;
    }

    public function delay(string $delay): static
    {
        $this->delay = $delay;

        return $this;
    }

    public function flat(bool $flat = true): static
    {
        $this->flat = $flat;

        return $this;
    }

    public function full(bool $full = true): static
    {
        $this->full = $full;

        return $this;
    }

    public function href(string $href): static
    {
        $this->href = $href;

        return $this;
    }

    public function icon(?string $icon = null): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function isLivewireComponent(bool $isLivewireComponent = true): static
    {
        $this->isLivewireComponent = $isLivewireComponent;

        return $this;
    }

    public function loading(string $loading): static
    {
        $this->loading = $loading;

        return $this;
    }

    public function outline(bool $outline = true): static
    {
        $this->outline = $outline;

        return $this;
    }

    public function position(string $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function round(bool $round = true): static
    {
        $this->round = $round;

        return $this;
    }

    public function size(?string $size = null): static
    {
        $this->size = $size;

        return $this;
    }

    public function square(bool $square = true): static
    {
        $this->square = $square;

        return $this;
    }

    public function text(?string $text = null): static
    {
        $this->text = $text;

        return $this;
    }

    public function toHtml(): ?string
    {
        if (! $this->shouldRender || ! $this->userHasTabPermission(false)) {
            return null;
        }

        $this->text = is_null($this->text) ? '' : $this->text;
        $button = new Button(
            text: $this->text,
            icon: $this->icon,
            position: $this->position,
            color: $this->color ?? 'indigo',
            square: $this->square,
            round: $this->round,
            href: $this->href,
            loading: $this->loading,
            delay: $this->delay,
            outline: $this->outline,
            flat: $this->flat,
            size: $this->size ?? 'md',
        );
        $button->attributes = new ComponentAttributeBag(
            array_merge([
                'wire:loading.attr' => 'readonly',
                'class' => 'border-b-2 text-secondary-600! !dark:text-secondary-400 border-b-transparent focus:ring-0! focus:ring-offset-0!',
                'x-bind:class' => "{'border-b-primary-600! rounded-b-none!': tab === '{$this->component}'}",
                'data-tab-name' => $this->component,
                'x-on:click.prevent' => 'tabButtonClicked($el)',
            ], $this->attributes)
        );

        return BladeCompiler::renderComponent($button);
    }

    public function userHasTabPermission(bool $throwException = true): bool
    {
        try {
            resolve_static(
                Permission::class,
                'findByName',
                [
                    'name' => 'tab.' . $this->component,
                ]
            );
        } catch (PermissionDoesNotExist) {
            return true;
        }

        if (! auth()->user()->can('tab.' . $this->component)) {
            if ($throwException) {
                throw UnauthorizedException::forPermissions(['tab.' . $this->component]);
            } else {
                return false;
            }
        }

        return true;
    }

    public function when(Closure|bool $condition): static
    {
        // when running in console, dont call the closure
        if (app()->runningInConsole()) {
            $this->shouldRender = true;

            return $this;
        }

        try {
            $this->shouldRender = (bool) value($condition);
        } catch (Throwable) {
            $this->shouldRender = false;
        }

        return $this;
    }

    public function wireModel(string $name): static
    {
        $this->wireModel = $name;

        return $this;
    }
}
