<x-modal name="edit-role-users">
    <x-card>
        <div class="space-y-6">
            @foreach($users as $user)
                <div class="flex">
                    <div class="flex-1 font-medium">{{ $user['name'] }}</div>
                    <div class="">
                        <x-checkbox wire:model="roleForm.users" :value="$user['id']"/>
                    </div>
                </div>
            @endforeach
        </div>
        <x-slot:footer>
            <div class="flex justify-end">
                <x-button flat :label="__('Cancel')" x-on:click="close"/>
                <x-button primary :label="__('Save')" wire:click="save().then((success) => { if(success) close(); })"/>
            </div>
        </x-slot:footer>
    </x-card>
</x-modal>

<x-modal name="edit-role-permissions">
    <x-card>
        <div class="space-y-6">
            <x-input wire:model="roleForm.name" :label="__('Name')"/>
            <div x-bind:class="$wire.roleForm.id && 'pointer-events-none'">
                <x-select
                    :label="__('Guard')"
                    :disabled="$roleForm->id ?? false"
                    :options="$guards"
                    wire:model="roleForm.guard_name"
                    x-bind:readonly="$wire.roleForm.id"
                />
            </div>
            <div>
                <x-label :label="__('Permissions')"/>
                <div x-show="$wire.roleForm.name !== 'Super Admin'" x-cloak>
                    <x-flux::checkbox-tree
                        wire:model="$entangle('roleForm.permissions')"
                        selectable="true"
                        tree="$wire.getPermissionTree()"
                        name-attribute="label"
                    />
                </div>
            </div>
        </div>
        <x-slot:footer>
            <div class="flex justify-end">
                <x-button flat :label="__('Cancel')" x-on:click="close"/>
                <x-button primary :label="__('Save')" wire:click="save().then((success) => { if(success) close(); })"/>
            </div>
        </x-slot:footer>
    </x-card>
</x-modal>
