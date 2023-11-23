<div>
    <div class="font-bold">
        {{$form['name']}}
    </div>
    <div class="font-light">
        {{$form['description']}}
    </div>
    @foreach($form['sections'] as $sectionIndex => $section)
        <div class="border border-gray-200 p-2 my-2">
            <div class="font-semibold">
                {{$section['name']}}
            </div>
            <div class="font-light py-2">
                {{$section['description']}}
            </div>
            @foreach($section['fields'] as $fieldIndex =>  $field)
                <div class="py-1">
                    @switch($field['type'])
                        @case('text')
                            <x-input type="text" label="{{$field['name']}}" placeholder="{{$field['description']}}" wire:model="$fieldResponses[{{$section['id']}}][{{$field['id']}}]" />
                            @break
                        @case('textarea')
                            <x-textarea label="{{$field['name']}}" placeholder="{{$field['description']}}" wire:model="$fieldResponses[{{$section['id']}}][{{$field['id']}}]" />
                            @break
                        @case('select')
                            <x-input type="select" name="{{$field['name']}}" placeholder="{{$field['description']}}" wire:model="$fieldResponses[{{$section['id']}}][{{$field['id']}}]" />
                            @break
                        @case('checkbox')
                            <x-checkbox label="{{$field['name']}}" placeholder="{{$field['description']}}" wire:model="$fieldResponses[{{$section['id']}}][{{$field['id']}}]" />
                            @break
                        @case('radio')
                            <x-input type="radio" label="{{$field['name']}}" placeholder="{{$field['description']}}" wire:model="$fieldResponses[{{$section['id']}}][{{$field['id']}}]" />
                            @break
                        @case('date')
                            <x-input type="date" label="{{$field['name']}}" placeholder="{{$field['description']}}" wire:model="$fieldResponses[{{$section['id']}}][{{$field['id']}}]" />
                            @break
                        @case('time')
                            <x-input type="time" label="{{$field['name']}}" placeholder="{{$field['description']}}" wire:model="$fieldResponses[{{$section['id']}}][{{$field['id']}}]" />
                            @break
                        @case('datetime')
                            <x-input type="datetime" label="{{$field['name']}}" placeholder="{{$field['description']}}" wire:model="$fieldResponses[{{$section['id']}}][{{$field['id']}}]" />
                            @break
                        @case('number')
                            <x-inputs.number label="{{$field['name']}}" placeholder="{{$field['description']}}" wire:model="$fieldResponses[{{$section['id']}}][{{$field['id']}}]" />
                            @break
                        @case('password')
                            <x-inputs.password label="{{$field['name']}}" placeholder="{{$field['description']}}" wire:model="$fieldResponses[{{$section['id']}}][{{$field['id']}}]" />
                            @break
                        @case('range')
                            <x-input type="range" name="{{$field['name']}}" id="{{$field['name']}}" wire:model="$fieldResponses[{{$section['id']}}][{{$field['id']}}]" />
                            @break
                        @default
                            <x-input label="{{$field['name']}}" placeholder="{{$field['description']}}" wire:model="$fieldResponses[{{$section['id']}}][{{$field['id']}}]" />
                    @endswitch
                </div>
            @endforeach
        </div>
    @endforeach
    <div class="flex justify-center">
        <x-button wire:click="submitForm" label="{{ __('Submit') }}"/>
    </div>
</div>
