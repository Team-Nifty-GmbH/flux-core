<x-modal id="edit-role-users-modal">
    <div class="flex flex-col gap-1.5">
        @foreach ($users as $user)
            <div class="flex">
                <div class="flex-1 font-medium">{{ $user['name'] }}</div>
                <div class="">
                    <x-checkbox
                        wire:model="roleForm.users"
                        :value="$user['id']"
                    />
                </div>
            </div>
        @endforeach
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose('edit-role-users-modal')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            wire:click="save().then((success) => { if(success) $modalClose('edit-role-users-modal'); })"
        />
    </x-slot>
</x-modal>

<x-modal id="edit-role-permissions-modal">
    <div class="flex flex-col gap-1.5">
        <x-input wire:model="roleForm.name" :text="__('Name')" />
        <div x-bind:class="$wire.roleForm.id && 'pointer-events-none'">
            <x-select.styled
                :label="__('Guard')"
                :disabled="$roleForm->id ?? false"
                wire:model="roleForm.guard_name"
                x-bind:readonly="$wire.roleForm.id"
                :options="$guards"
            />
        </div>
        <div>
            <x-label :label="__('Permissions')" />
            <div x-show="$wire.roleForm.name !== 'Super Admin'" x-cloak>
                <x-flux::checkbox-tree
                    wire:model="$entangle('roleForm.permissions')"
                    selectable="true"
                    tree="$wire.permissions"
                    name-attribute="label"
                    :with-search="true"
                    :search-attributes="['path', 'label']"
                />
            </div>
        </div>
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose('edit-role-permissions-modal')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            wire:click="save().then((success) => { if(success) $modalClose('edit-role-permissions-modal'); })"
        />
    </x-slot>
</x-modal>
