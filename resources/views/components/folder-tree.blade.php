<ul>
    @foreach($tree as $treeItem)
        @if(array_key_exists('children', $treeItem))
            <li >
                <div class="block flex rounded py-1 pl-5" >
                    <div x-on:click="addOrRemove('{{ $treeItem['collection_name'] }}')" class="grid grid-cols-2 items-center gap-x-0.5" >
                        <x-icon x-bind:class="{ 'rotate-90': open.indexOf('{{ $treeItem['collection_name'] }}') !== -1 }" name="chevron-right" class="h-4 w-4 transition" />
                        <x-icon name="folder" class="h-4 w-4" />
                    </div>
                    <div class="cursor-default pl-2"
                         @if(auth()->user()->can('api.media.put'))
                             wire:click="showFolder('{{ $treeItem['collection_name'] }}', {{ (int)$treeItem['is_static'] }})"
                           @endif
                    >{{ $treeItem['name'] }}</div>
                </div>
                    <ul
                        class="pl-5 pb-1 transition"
                        x-show="open.indexOf('{{ $treeItem['collection_name'] }}') !== -1"
                        style="display: none;"
                    >
                        <x-folder-tree :tree="$treeItem['children']" />
                    </ul>
            </li>
        @else
            <li>
                <div class="has-children block flex cursor-default py-1 pl-5" wire:click="select({{ $treeItem['id'] }})">
                    <x-icon name="document" class="h-4 w-4" />
                    <span class="pl-2">{{ $treeItem['name'] }}</span>
                </div>
            </li>
        @endif
   @endforeach
</ul>
