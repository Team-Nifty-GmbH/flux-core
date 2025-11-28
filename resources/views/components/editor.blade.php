@props([
    "editable" => true,
])

<div>
    <div @if(!$tooltipDropdown) class="mb-1" @endif>
        <x-label :text="$label ?? ''" />
    </div>
    <div
        @if ($fullHeight)
            class="h-full"
        @endif
        @if ($attributes->has("x-modelable"))
            x-modelable="{{ $attributes->get("x-modelable") }}"
        @else
            x-modelable="editable"
        @endif
        x-data="Object.assign({}, setupEditor(
                @if ($attributes->wire("model")->value())
                    $wire.$entangle('{{ $attributes->wire("model")->value() }}',
                    @js($attributes->wire("model")->hasModifier("live"))
                    ),
                    {{
                        $attributes->wire("model")->hasModifier("debounce")
                            ? Str::before(
                                $attributes->wire("model")->modifiers()[
                                    $attributes
                                        ->wire("model")
                                        ->modifiers()
                                        ->search("debounce") + 1
                                ],
                                "ms",
                            )
                            : 0
                    }}
                @endif
            )(), {
                showBladeVariables: false,
                bladeVariables: @js($bladeVariables)
                })"
        x-init="initTextArea('{{ $id }}',$refs['editor-{{ $id }}'], @json($transparent), @json($tooltipDropdown), @json($fullHeight), @json($showEditorPadding), @json($defaultFontSize))"
        {{ $attributes->whereDoesntStartWith("wire:model") }}
        wire:ignore
        {{-- add button for blade variable dropdown --}}
        @if ($tooltipDropdown)
            x-on:mouseenter="showBladeVariables = true"
            x-on:mouseleave="showBladeVariables = false"
        @endif
    >
        <div
            x-cloak
            x-transition
            x-show="proxy.isEditable"
            x-ref="controlPanel-{{ $id }}"
            id="controlPanel"
            class="{{ $tooltipDropdown ? "" : "border border-b-0" }} flex w-full flex-wrap items-stretch rounded-t-md border-secondary-300 placeholder-secondary-400 transition duration-100 ease-in-out focus:border-primary-500 focus:outline-none focus:ring-primary-500 sm:text-sm dark:border-secondary-600 dark:bg-secondary-800 dark:text-secondary-400 dark:placeholder-secondary-500"
        ></div>
        <div class="relative list-disc" x-ref="editor-{{ $id }}">
            {{-- Tooltip buttons (rendered via buttonInstance->render() when tooltipDropdown is true) --}}
            @if ($tooltipDropdown)
                <div
                    x-cloak
                    x-show="proxy.isEditable"
                    class="absolute right-0 top-0 z-10 flex gap-1 p-2"
                >
                    @foreach ($buttonInstances as $buttonInstance)
                        @if ($buttonInstance instanceof \FluxErp\Contracts\EditorTooltipButton)
                            @if ($buttonInstance instanceof \Livewire\Component)
                                <livewire:dynamic-component
                                    :is="$buttonInstance::class"
                                />
                            @else
                                {!! $buttonInstance->render() !!}
                            @endif
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    {{-- templates to be add on demand --}}
    <template
        x-ref="popWindow-{{ $id }}"
        class="flex w-full flex-wrap items-stretch divide-x rounded-t-md placeholder-secondary-400 transition duration-100 ease-in-out focus:border-primary-500 focus:outline-none focus:ring-primary-500 sm:text-sm dark:border-secondary-600 dark:bg-secondary-800 dark:text-secondary-400 dark:placeholder-secondary-500"
    ></template>
    <template x-ref="commands-{{ $id }}">
        @foreach ($buttonInstances as $buttonInstance)
            @if (

                (! $tooltipDropdown ||
                    ! $buttonInstance instanceof \FluxErp\Contracts\EditorDropdownButton) &&
                ! (
                    $tooltipDropdown &&
                    $buttonInstance instanceof \FluxErp\Contracts\EditorTooltipButton
                )            )
                @if ($buttonInstance instanceof \Livewire\Component)
                    <livewire:dynamic-component :is="$buttonInstance::class" />
                @else
                    {!! $buttonInstance->render() !!}
                @endif
            @endif
        @endforeach

        @if ($tooltipDropdown)
            <div class="flex w-full flex-col gap-1 pt-2">
                @foreach ($collapsibleInstances as $collapsible)
                    <div x-data="tiptapExpandable()">
                        <x-button
                            x-cloak
                            x-show="!expanded"
                            class="w-full"
                            x-on:click.prevent="toggle"
                            :text="__($collapsible->tooltip())"
                            flat
                            icon="chevron-right"
                            position="right"
                            color="secondary"
                        />
                        <x-button
                            x-cloak
                            x-show="expanded"
                            class="w-full"
                            x-on:click.prevent="toggle"
                            :text="__($collapsible->tooltip())"
                            flat
                            icon="chevron-down"
                            position="right"
                            color="primary"
                        />
                        <div
                            x-collapse
                            x-cloak
                            x-show="expanded"
                            class="pt-2"
                        >
                            @foreach ($collapsible->dropdownContent() as $action)
                                {!! $action->render() !!}
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </template>
    {{-- Dropdown templates for dropdown buttons --}}
    @foreach ($buttonInstances as $buttonInstance)
        @if ($buttonInstance instanceof \FluxErp\Contracts\EditorDropdownButton)
            @if (! $tooltipDropdown || $buttonInstance instanceof \FluxErp\Contracts\EditorTooltipButton)
                <template x-ref="{{ $buttonInstance->dropdownRef() }}Dropdown">
                    <div class="flex flex-col">
                        @foreach ($buttonInstance->dropdownContent() as $dropdownButton)
                            {!! $dropdownButton->render() !!}
                        @endforeach
                    </div>
                </template>
            @endif
        @endif
    @endforeach
</div>
