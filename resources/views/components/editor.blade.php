@props([
    "editable" => true,
])

<div>
    <div class="mb-1">
        <x-label :text="$label ?? ''" />
    </div>
    <div
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
            )(), { bladeVariables: @js($bladeVariables) })"
        x-init="initTextArea('{{ $id }}',$refs['editor-{{ $id }}'], @json($transparent), @json($tooltipDropdown), @json($defaultFontSize))"
        {{ $attributes->whereDoesntStartWith("wire:model") }}
        wire:ignore
    >
        <div
            x-cloak
            x-transition
            x-show="proxy.isEditable"
            x-ref="controlPanel-{{ $id }}"
            id="controlPanel"
            class="{{ $tooltipDropdown ? "" : "border border-b-0" }} flex w-full flex-wrap items-stretch rounded-t-md border-secondary-300 placeholder-secondary-400 transition duration-100 ease-in-out focus:border-primary-500 focus:outline-none focus:ring-primary-500 sm:text-sm dark:border-secondary-600 dark:bg-secondary-800 dark:text-secondary-400 dark:placeholder-secondary-500"
        ></div>
        <div class="list-disc" x-ref="editor-{{ $id }}"></div>
    </div>
    {{-- templates to be add on demand --}}
    <template
        x-ref="popWindow-{{ $id }}"
        class="flex w-full flex-wrap items-stretch divide-x rounded-t-md placeholder-secondary-400 transition duration-100 ease-in-out focus:border-primary-500 focus:outline-none focus:ring-primary-500 sm:text-sm dark:border-secondary-600 dark:bg-secondary-800 dark:text-secondary-400 dark:placeholder-secondary-500"
    ></template>
    <template x-ref="commands-{{ $id }}">
        @if ($bold)
            <x-button
                flat
                color="secondary"
                x-on:click="editor().chain().focus().toggleBold().run()"
                class="font-bold"
                text="B"
            />
        @endif

        @if ($italic)
            <x-button
                flat
                color="secondary"
                x-on:click="editor().chain().focus().toggleItalic().run()"
                class="font-italic"
                text="I"
            />
        @endif

        @if ($underline)
            <x-button
                flat
                color="secondary"
                x-on:click="editor().chain().focus().toggleUnderline().run()"
                class="underline"
                text="U"
            />
        @endif

        @if ($strike)
            <x-button
                flat
                color="secondary"
                x-on:click="editor().chain().focus().toggleStrike().run()"
                class="line-through"
                text="S"
            />
        @endif

        @if ($code)
            <x-button
                flat
                color="secondary"
                x-on:click="editor().chain().focus().toggleCode().run()"
                icon="code-bracket"
                :text="null"
            />
        @endif

        @if ($h1)
            <x-button
                flat
                color="secondary"
                x-on:click="editor().chain().focus().toggleHeading({ level: 1 }).run()"
                text="H1"
            />
        @endif

        @if ($h2)
            <x-button
                flat
                color="secondary"
                x-on:click="editor().chain().focus().toggleHeading({ level: 2 }).run()"
                text="H2"
            />
        @endif

        @if ($h3)
            <x-button
                flat
                color="secondary"
                x-on:click="editor().chain().focus().toggleHeading({ level: 3 }).run()"
                text="H3"
            />
        @endif

        @if ($availableFontSizes && ! $tooltipDropdown)
            <x-button
                x-on:click.prevent="onClick"
                x-ref="tippyParent-fontSize-{{ $id }}"
                x-data="editorFontSizeColorHandler($refs['tippyParent-fontSize-{{ $id }}'], $refs['fontSizeDropdown-{{ $id }}'])"
                flat
                color="secondary"
            >
                <x-slot:text>
                    <i class="ph ph-text-aa text-lg"></i>
                </x-slot>
            </x-button>
        @endif

        @if ($textColors && ! $tooltipDropdown)
            <x-button
                x-on:click.prevent="onClick"
                x-ref="tippyParent-color-{{ $id }}"
                flat
                icon="paint-brush"
                x-data="editorFontSizeColorHandler($refs['tippyParent-color-{{ $id }}'], $refs['colorDropDown-{{$id}}'])"
                color="secondary"
            />
        @endif

        @if ($textBackgroundColors && ! $tooltipDropdown)
            <x-button
                x-on:click.prevent="onClick"
                x-ref="tippyParent-background-color-{{ $id }}"
                flat
                icon="swatch"
                x-data="editorFontSizeColorHandler($refs['tippyParent-background-color-{{ $id }}'], $refs['backgroundColorDropDown-{{ $id }}'])"
                color="secondary"
            />
        @endif

        <template
            x-if="Object.values(bladeVariables).length > 0 && ! @js($tooltipDropdown)"
        >
            <x-button
                x-on:click.prevent="onClick"
                x-ref="tippyParent-blade-variables-{{ $id }}"
                flat
                icon="variable"
                x-data="editorFontSizeColorHandler($refs['tippyParent-blade-variables-{{ $id }}'], $refs['bladeVariablesDropdown-{{ $id }}'])"
                color="secondary"
            />
        </template>

        @if ($availableFontSizes && $tooltipDropdown)
            @foreach ($availableFontSizes as $size)
                <x-button
                    flat
                    color="secondary"
                    :text="$size . 'px'"
                    x-on:click="editor().chain().focus().setFontSize({{ json_encode($size) }}).run()"
                />
            @endforeach
        @endif

        @if ($horizontalRule)
            <x-button
                flat
                color="secondary"
                x-on:click="editor().chain().focus().setHorizontalRule().run()"
                text="-"
            />
        @endif

        @if ($bulletList)
            <x-button
                flat
                color="secondary"
                icon="list-bullet"
                x-on:click="editor().chain().focus().toggleBulletList().run()"
            />
        @endif

        @if ($orderedList)
            <x-button
                flat
                color="secondary"
                icon="list-bullet"
                x-on:click="editor().chain().focus().toggleOrderedList().run()"
            />
        @endif

        @if ($quote)
            <x-button
                flat
                color="secondary"
                x-on:click="editor().chain().focus().toggleBlockquote().run()"
                text="„“"
            />
        @endif

        @if ($codeBlock)
            <x-button
                flat
                color="secondary"
                icon="code-bracket-square"
                x-on:click="editor().chain().focus().toggleCodeBlock().run()"
            />
        @endif

        @if ($tooltipDropdown && $textColors)
            <div class="mb-2 flex flex-col items-center text-gray-600">
                <div class="w-full text-left">{{ __("Text Color") }}:</div>
                <x-button
                    x-on:click="editor().chain().focus().unsetColor().run()"
                    class="mb-1 w-full"
                    flat
                    color="neutral"
                    :text="__('Remove Color')"
                />
                <div class="flex space-x-1">
                    @foreach ($textColors as $color)
                        <div class="flex flex-col gap-1">
                            @foreach ($color as $shade)
                                <div
                                    x-on:click="editor().chain().focus().setColor({{ json_encode($shade) }}).run()"
                                    class="min-h-6 min-w-6 cursor-pointer"
                                    style="background-color: {{ $shade }}"
                                ></div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($tooltipDropdown && $textBackgroundColors)
            <div class="mb-2 flex flex-col items-center text-gray-600">
                <div class="w-full text-left">
                    {{ __("Text Background Color") }}:
                </div>
                <x-button
                    x-on:click="editor().chain().focus().unsetBackgroundColor().run()"
                    class="mb-1 w-full"
                    flat
                    color="neutral"
                    :text="__('Remove Color')"
                />
                <div class="flex space-x-1">
                    @foreach ($textBackgroundColors as $color)
                        <div class="flex flex-col gap-1">
                            @foreach ($color as $shade)
                                <div
                                    x-on:click="editor().chain().focus().setBackgroundColor({{ json_encode($shade) }}).run()"
                                    class="min-h-6 min-w-6 cursor-pointer"
                                    style="background-color: {{ $shade }}"
                                ></div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </template>
    <template x-ref="fontSizeDropdown-{{ $id }}">
        <div class="flex flex-col">
            @foreach ($availableFontSizes as $size)
                <x-button
                    flat
                    color="secondary"
                    :text="$size . 'px'"
                    x-on:click="editor().chain().focus().setFontSize({{ json_encode($size) }}).run()"
                />
            @endforeach
        </div>
    </template>
    <template x-ref="colorDropDown-{{ $id }}">
        <div class="p-1">
            <x-button
                x-on:click="editor().chain().focus().unsetColor().run()"
                class="mb-1 w-full"
                flat
                color="neutral"
                :text="__('Remove Color')"
            />
            <div class="flex space-x-1">
                @foreach ($textColors as $color)
                    <div class="flex flex-col gap-1">
                        @foreach ($color as $shade)
                            <div
                                x-on:click="editor().chain().focus().setColor({{ json_encode($shade) }}).run()"
                                class="min-h-6 min-w-6 cursor-pointer"
                                style="background-color: {{ $shade }}"
                            ></div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </template>
    <template x-ref="backgroundColorDropDown-{{ $id }}">
        <div class="p-1">
            <x-button
                x-on:click="editor().chain().focus().unsetBackgroundColor().run()"
                class="mb-1 w-full"
                flat
                color="neutral"
                :text="__('Remove Color')"
            />
            <div class="flex space-x-1">
                @foreach ($textBackgroundColors as $color)
                    <div class="flex flex-col gap-1">
                        @foreach ($color as $shade)
                            <div
                                x-on:click="editor().chain().focus().setBackgroundColor({{ json_encode($shade) }}).run()"
                                class="min-h-6 min-w-6 cursor-pointer"
                                style="background-color: {{ $shade }}"
                            ></div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </template>
    <template x-ref="bladeVariablesDropdown-{{ $id }}">
        <div class="flex max-h-64 w-64 flex-col gap-1 overflow-y-auto p-2">
            <template x-for="variable in Object.values(bladeVariables)">
                <button
                    type="button"
                    class="group inline-flex items-center justify-start gap-1 rounded px-2 py-1.5 text-xs text-slate-600 outline-none transition-all duration-150 ease-in hover:bg-slate-100 hover:shadow-sm focus:ring-2 focus:ring-slate-300 dark:text-slate-300 dark:hover:bg-slate-700 dark:focus:ring-slate-600"
                    x-on:click="
                        editor()
                            .chain()
                            .focus()
                            .insertContent([
                                {
                                    type: 'bladeVariable',
                                    attrs: { label: variable.label, value: variable.value },
                                },
                                { type: 'text', text: ' ' },
                            ])
                            .run()
                    "
                >
                    <span
                        class="inline-flex items-center rounded bg-indigo-100 px-1.5 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300"
                        x-text="variable.label"
                    ></span>
                </button>
            </template>
        </div>
    </template>
</div>
