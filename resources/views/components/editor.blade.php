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
        x-init="initTextArea('{{ $id }}',$refs['editor-{{ $id }}'], @json($transparent), @json($tooltipDropdown), @json($fullHeight), @json($showEditorPadding))"
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
            {{-- button on input hover in case toolTip is true --}}
            @if ($tooltipDropdown)
                <div
                    x-cloak
                    x-show="
                        proxy.isEditable &&
                            showBladeVariables &&
                            Object.values(bladeVariables).length > 0
                    "
                    class="absolute right-0 top-0 z-10 p-2"
                    x-data="floatingUiDropdown($refs['tippyParent-blade-variables-on-hover-{{ $id }}'], $refs['bladeVariablesDropdown-{{ $id }}'])"
                >
                    <x-button.circle
                        icon="plus"
                        x-on:click.prevent="onClick"
                        x-ref="tippyParent-blade-variables-on-hover-{{ $id }}"
                    />
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
            @if (! $tooltipDropdown || ! $buttonInstance instanceof \FluxErp\Contracts\EditorDropdownButton)
                {!! $buttonInstance->render() !!}
            @endif
        @endforeach

        @if ($tooltipDropdown)
            <div class="flex w-full flex-col gap-1 pt-2">
                @foreach ($tooltipDropdownContent as $expandableContent)
                    <div x-data="tiptapExpandable()">
                        <x-button
                            x-show="!expanded"
                            class="w-full"
                            x-on:click.prevent="toggle"
                            :text='__($expandableContent->tooltip())'
                            position="right"
                            flat
                            icon="chevron-down"
                            color="secondary"
                        />
                        <x-button
                            x-show="expanded"
                            class="w-full"
                            x-on:click.prevent="toggle"
                            :text='__($expandableContent->tooltip())'
                            position="right"
                            flat
                            icon="chevron-right"
                            color="primary"
                        />
                        <div
                            x-collapse.duration.200ms
                            x-show="expanded"
                            class="{{ $expandableContent->tooltip() === "Table" ? "flex flex-col" : "" }} pt-2"
                        >
                            @foreach ($expandableContent->dropdownContent() as $dropdownButton)
                                {!! $dropdownButton->render() !!}
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </template>
    {{-- Dropdown templates for dropdown buttons --}}
    @if (! $tooltipDropdown)
        @foreach ($buttonInstances as $buttonInstance)
            @if ($buttonInstance instanceof \FluxErp\Contracts\EditorDropdownButton)
                <template x-ref="{{ $buttonInstance->dropdownRef() }}Dropdown">
                    <div class="flex flex-col">
                        @foreach ($buttonInstance->dropdownContent() as $dropdownButton)
                            {!! $dropdownButton->render() !!}
                        @endforeach
                    </div>
                </template>
            @endif
        @endforeach
    @endif
</div>
