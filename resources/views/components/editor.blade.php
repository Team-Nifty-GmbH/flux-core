@props([
    "editable" => true,
])

<div>
    <div class="mb-1">
        <x-label :label="$label ?? ''" />
    </div>
    <div
        @if ($attributes->has("x-modelable"))
            x-modelable="{{ $attributes->get("x-modelable") }}"
        @else
            x-modelable="editable"
        @endif
        x-data="{
            ...setupEditor(
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
            ),
        }"
        x-init="initTextArea($refs.editor, @json($transparent), @json($tooltipDropdown))"
        {{ $attributes->whereDoesntStartWith("wire:model") }}
        wire:ignore
    >
        <template
            x-ref="popWindow"
            class="placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500 dark:border-secondary-600 dark:bg-secondary-800 dark:text-secondary-400 dark:placeholder-secondary-500 flex w-full flex-wrap items-stretch divide-x rounded-t-md transition duration-100 ease-in-out focus:outline-none sm:text-sm"
        ></template>
        <div
            x-cloak
            x-transition
            x-show="proxy.isEditable"
            x-ref="controlPanel"
            id="controlPanel"
            class="{{ $tooltipDropdown ? "" : "border border-b-0" }} border-secondary-300 placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500 dark:border-secondary-600 dark:bg-secondary-800 dark:text-secondary-400 dark:placeholder-secondary-500 flex w-full flex-wrap items-stretch divide-x rounded-t-md transition duration-100 ease-in-out focus:outline-none sm:text-sm"
        ></div>
        <div class="list-disc" x-ref="editor"></div>
    </div>
    <template x-ref="commands">
        @if ($bold)
            <x-button
                flat
                color="secondary"
                x-on:click="editor().chain().focus().toggleBold().run()"
                class="font-bold"
                text="B"
            ></x-button>
        @endif

        @if ($italic)
            <x-button
                flat
                color="secondary"
                x-on:click="editor().chain().focus().toggleItalic().run()"
                class="font-italic"
                text="I"
            ></x-button>
        @endif

        @if ($strike)
            <x-button
                flat
                color="secondary"
                x-on:click="editor().chain().focus().toggleStrike().run()"
                class="line-through"
                text="S"
            ></x-button>
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
            ></x-button>
        @endif

        @if ($h2)
            <x-button
                flat
                color="secondary"
                x-on:click="editor().chain().focus().toggleHeading({ level: 2 }).run()"
                text="H2"
            ></x-button>
        @endif

        @if ($h3)
            <x-button
                flat
                color="secondary"
                x-on:click="editor().chain().focus().toggleHeading({ level: 3 }).run()"
                text="H3"
            ></x-button>
        @endif

        @if ($horizontalRule)
            <x-button
                flat
                color="secondary"
                x-on:click="editor().chain().focus().setHorizontalRule().run()"
                text="-"
            ></x-button>
        @endif

        @if ($bulletList)
            <x-button
                flat
                color="secondary"
                icon="list-bullet"
                x-on:click="editor().chain().focus().toggleBulletList().run()"
            ></x-button>
        @endif

        @if ($orderedList)
            <x-button
                flat
                color="secondary"
                icon="list-bullet"
                x-on:click="editor().chain().focus().toggleOrderedList().run()"
            ></x-button>
        @endif

        @if ($quote)
            <x-button
                flat
                color="secondary"
                x-on:click="editor().chain().focus().toggleBlockquote().run()"
                text="„“"
            ></x-button>
        @endif

        @if ($codeBlock)
            <x-button
                flat
                color="secondary"
                icon="code-bracket-square"
                x-on:click="editor().chain().focus().toggleCodeBlock().run()"
            ></x-button>
        @endif
    </template>
</div>
