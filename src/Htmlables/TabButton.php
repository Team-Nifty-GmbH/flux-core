<?php

namespace FluxErp\Htmlables;

use FluxErp\Models\Permission;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\ComponentAttributeBag;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Exceptions\UnauthorizedException;
use WireUi\View\Components\Button;

class TabButton implements Htmlable
{
    protected bool $shouldRender = true;

    public static function make(
        string $component,
        bool $rounded = false,
        bool $squared = false,
        bool $outline = false,
        bool $flat = true,
        bool $full = false,
        ?string $color = null,
        ?string $size = null,
        ?string $label = null,
        ?string $icon = null,
        ?string $rightIcon = null,
        ?string $spinner = null,
        ?string $loadingDelay = null,
        ?string $href = null,
        bool $isLivewireComponent = false,
        ?string $wireModel = null,
        ?array $attributes = []
    ): static {
        return new static(
            component: $component,
            rounded: $rounded,
            squared: $squared,
            outline: $outline,
            flat: $flat,
            full: $full,
            color: $color,
            size: $size,
            label: $label,
            icon: $icon,
            rightIcon: $rightIcon,
            spinner: $spinner,
            loadingDelay: $loadingDelay,
            href: $href,
            isLivewireComponent: $isLivewireComponent,
            wireModel: $wireModel,
            attributes: $attributes,
        );
    }

    public function __construct(
        public string $component,
        public bool $rounded = false,
        public bool $squared = false,
        public bool $outline = false,
        public bool $flat = true,
        public bool $full = false,
        public ?string $color = null,
        public ?string $size = null,
        public ?string $label = null,
        public ?string $icon = null,
        public ?string $rightIcon = null,
        public ?string $spinner = null,
        public ?string $loadingDelay = null,
        public ?string $href = null,
        public bool $isLivewireComponent = false,
        public ?string $wireModel = null,
        public ?array $attributes = []
    ) {}

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

    public function when(\Closure|bool $condition): static
    {
        // when running in console, dont call the closure
        if (app()->runningInConsole()) {
            $this->shouldRender = true;

            return $this;
        }

        try {
            $this->shouldRender = (bool) value($condition);
        } catch (\Throwable) {
            $this->shouldRender = false;
        }

        return $this;
    }

    public function toHtml(): ?string
    {
        if (! $this->shouldRender || ! $this->userHasTabPermission(false)) {
            return null;
        }

        $this->label = is_null($this->label) ? '' : $this->label;
        $button = new Button(
            rounded: $this->rounded,
            squared: $this->squared,
            outline: $this->outline,
            flat: $this->flat,
            full: $this->full,
            color: $this->color,
            size: $this->size,
            label: $this->label,
            icon: $this->icon,
            rightIcon: $this->rightIcon,
            spinner: $this->spinner,
            loadingDelay: $this->loadingDelay,
            href: $this->href,
        );
        $button->attributes = new ComponentAttributeBag(
            array_merge([
                'wire:loading.attr' => 'readonly',
                'class' => 'border-b-2 border-b-transparent focus:!ring-0 focus:!ring-offset-0',
                'x-bind:class' => "{'!border-b-primary-600 rounded-b-none': tab === '{$this->component}'}",
                'data-tab-name' => $this->component,
                'x-on:click.prevent' => 'tabButtonClicked($el)',
            ], $this->attributes)
        );

        return BladeCompiler::renderComponent($button);
    }

    public function isLivewireComponent(bool $isLivewireComponent = true): static
    {
        $this->isLivewireComponent = $isLivewireComponent;

        return $this;
    }

    public function wireModel(string $name): static
    {
        $this->wireModel = $name;

        return $this;
    }

    public function attributes(array $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function rounded(bool $rounded = true): static
    {
        $this->rounded = $rounded;

        return $this;
    }

    public function squared(bool $squared = true): static
    {
        $this->squared = $squared;

        return $this;
    }

    public function outline(bool $outline = true): static
    {
        $this->outline = $outline;

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

    public function color(?string $color = null): static
    {
        $this->color = $color;

        return $this;
    }

    public function size(?string $size = null): static
    {
        $this->size = $size;

        return $this;
    }

    public function label(?string $label = null): static
    {
        $this->label = $label;

        return $this;
    }

    public function icon(?string $icon = null): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function rightIcon(string $rightIcon): static
    {
        $this->rightIcon = $rightIcon;

        return $this;
    }

    public function spinner(string $spinner): static
    {
        $this->spinner = $spinner;

        return $this;
    }

    public function loadingDelay(string $loadingDelay): static
    {
        $this->loadingDelay = $loadingDelay;

        return $this;
    }

    public function href(string $href): static
    {
        $this->href = $href;

        return $this;
    }
}
