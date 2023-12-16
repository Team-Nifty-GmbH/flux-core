<div>
    <x-label class="mb-1">
        {{ $label ?? '' }}
    </x-label>
    <div
        x-modelable="editable"
        x-data="{
            ...setupEditor(@if($attributes->wire('model')->value()) $wire.entangle('{{ $attributes->wire('model')->value() }}') @endif),
        }"
        x-init="() => init($refs.editor)"
        {{ $attributes->whereDoesntStartWith('wire:model') }}
        wire:ignore
    >
        <div x-cloak x-transition x-show="proxy.isEditable" class="divide-x flex flex-wrap items-stretch placeholder-secondary-400 dark:bg-secondary-800 dark:text-secondary-400 dark:placeholder-secondary-500 border border-b-0 border-secondary-300 focus:ring-primary-500 focus:border-primary-500 dark:border-secondary-600 block w-full sm:text-sm rounded-t-md transition ease-in-out duration-100 focus:outline-none">
            @if($bold)
                <x-button flat squared x-on:click="editor().chain().focus().toggleBold().run()" class="font-bold" label="B"></x-button>
            @endif
            @if($italic)
                <x-button flat squared x-on:click="editor().chain().focus().toggleItalic().run()" class="font-italic" label="I"></x-button>
            @endif
            @if($strike)
                <x-button flat squared x-on:click="editor().chain().focus().toggleStrike().run()" class="line-through" label="S"></x-button>
            @endif
            @if($code)
                <x-button flat squared x-on:click="editor().chain().focus().toggleCode().run()" icon="code" :label="null"/>
            @endif
            @if($h1)
                <x-button flat squared x-on:click="editor().chain().focus().toggleHeading({ level: 1 }).run()" label="H1"></x-button>
            @endif
            @if($h2)
                <x-button flat squared x-on:click="editor().chain().focus().toggleHeading({ level: 2 }).run()" label="H2"></x-button>
            @endif
            @if($h3)
                <x-button flat squared x-on:click="editor().chain().focus().toggleHeading({ level: 3 }).run()" label="H3"></x-button>
            @endif

            @if($horizontalRule)
                <x-button flat squared x-on:click="editor().chain().focus().setHorizontalRule().run()" label="-"></x-button>
            @endif

            @if($bulletList)
                <x-button flat squared x-on:click="editor().chain().focus().toggleBulletList().run()">
                    <x-slot:label>
                        <x-heroicons name="list-bullet" class="w-4 h-4"/>
                    </x-slot:label>
                </x-button>
            @endif
            @if($orderedList)
                <x-button flat squared x-on:click="editor().chain().focus().toggleOrderedList().run()">
                    <x-slot:label>
                        <x-heroicons name="list-bullet" class="w-4 h-4"/>
                    </x-slot:label>
                </x-button>
            @endif
            @if($quote)
                <x-button flat squared x-on:click="editor().chain().focus().toggleBlockquote().run()" label="„“" />
            @endif
            @if($codeBlock)
                <x-button flat squared x-on:click="editor().chain().focus().toggleCodeBlock().run()">
                    <x-slot:label>
                        <x-heroicons name="code-bracket-square" class="w-4 h-4"/>
                    </x-slot:label>
                </x-button>
            @endif
        </div>
        <div class="list-disc" x-ref="editor"></div>
    </div>
</div>
