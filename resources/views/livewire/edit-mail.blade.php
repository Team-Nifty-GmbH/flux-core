<div x-data="{
    addReceiver($event, type) {
        let value = $event.target.value;
        if ($event instanceof KeyboardEvent && $event.which !== 13) {
            value = value.slice(0, -1);
        }

        value = value.trim();

        if (value && ($event instanceof FocusEvent || ($event.code === 'Comma' || $event.code === 'Enter' || $event.code === 'Space'))) {
            const email = value.match(/<([^>]*)>/);
            if (email && email[1]) {
                value = email[1];
            }
            $wire.mailMessage[type].push(value);
            $event.target.value = null;
        }
    }
}">
    <x-modal width="7xl" name="edit-mail" x-on:close="$wire.clear()">
        <x-card class="flex flex-col gap-4 w-full">
            <div class="flex flex-col gap-1.5">
                <x-label>{{ __('To') }}</x-label>
                <div class="flex gap-1" x-cloak x-show="! $wire.multiple">
                    <template x-for="to in $wire.mailMessage.to || []">
                        <x-badge flat primary cl>
                            <x-slot:label>
                                <span x-text="to"></span>
                            </x-slot:label>
                            <x-slot
                                name="append"
                                class="relative flex items-center w-2 h-2"
                            >
                                <button
                                    type="button"
                                    x-on:click="$wire.mailMessage.to.splice($wire.mailMessage.to.indexOf(to), 1)"
                                    x-bind:disabled="$wire.multiple"
                                >
                                    <x-icon
                                        name="x-mark"
                                        class="w-4 h-4"
                                    />
                                </button>
                            </x-slot>
                        </x-badge>
                    </template>
                </div>
                <x-input
                    :placeholder="__('Add a new to')"
                    x-on:blur="addReceiver($event, 'to')"
                    x-on:keyup="addReceiver($event, 'to')"
                    class="w-full"
                    x-bind:disabled="$wire.multiple"
                />
            </div>
            <div class="flex flex-col gap-1.5">
                <x-label>{{ __('CC') }}</x-label>
                <div class="flex gap-1" x-cloak x-show="! $wire.multiple">
                    <template x-for="cc in $wire.mailMessage.cc || []">
                        <x-badge flat primary cl>
                            <x-slot:label>
                                <span x-text="cc"></span>
                            </x-slot:label>
                            <x-slot
                                name="append"
                                class="relative flex items-center w-2 h-2"
                            >
                                <button
                                    type="button"
                                    x-on:click="$wire.mailMessage.cc.splice($wire.mailMessage.cc.indexOf(to), 1)"
                                >
                                    <x-icon
                                        name="x-mark"
                                        class="w-4 h-4"
                                    />
                                </button>
                            </x-slot>
                        </x-badge>
                    </template>
                </div>
                <x-input :placeholder="__('Add a new cc')" x-on:blur="addReceiver($event, 'cc')" x-on:keyup="addReceiver($event, 'cc')" class="w-full" />
            </div>
            <div class="flex flex-col gap-1.5">
                <x-label>{{ __('BCC') }}</x-label>
                <div class="flex gap-1">
                    <template x-for="bcc in $wire.mailMessage.bcc || []">
                        <x-badge flat primary cl>
                            <x-slot:label>
                                <span x-text="bcc"></span>
                            </x-slot:label>
                            <x-slot
                                name="append"
                                class="relative flex items-center w-2 h-2"
                            >
                                <button
                                    type="button"
                                    x-on:click="$wire.mailMessage.bcc.splice($wire.mailMessage.bcc.indexOf(to), 1)"
                                >
                                    <x-icon
                                        name="x-mark"
                                        class="w-4 h-4"
                                    />
                                </button>
                            </x-slot>
                        </x-badge>
                    </template>
                </div>
                <x-input :placeholder="__('Add a new bcc')" x-on:blur="addReceiver($event, 'bcc')" x-on:keyup="addReceiver($event, 'bcc')" class="w-full" />
            </div>
            <div class="grow">
                <x-input wire:model="mailMessage.subject" class="w-full" :label="__('Subject')"/>
            </div>
            <x-select
                :clearable="false"
                :options="$mailAccounts"
                wire:model="mailMessage.mail_account_id"
                :label="__('Send From')"
                option-label="email"
                option-value="id"
            />
            <div>
                <x-label>{{ __('Attachments') }}</x-label>
                <label for="files">
                    <div class="flex gap-1 min-h-[2rem] w-full rounded-md bg-gray-100 p-1.5">
                        <template x-for="file in $wire.mailMessage.attachments">
                            <x-badge white rounded>
                                <x-slot:prepend>
                                    <x-icon name="paper-clip" class="w-4 h-4"/>
                                </x-slot:prepend>
                                <x-slot:label>
                                    <div wire:click.prevent="downloadAttachment(file.id)" class="cursor-pointer">
                                        <span x-text="file.name"></span>
                                    </div>
                                </x-slot:label>
                                <x-slot:append>
                                    <button type="button" x-on:click.prevent="$wire.mailMessage.attachments.splice($wire.mailMessage.attachments.indexOf(file), 1)">
                                        <x-icon name="x-mark" class="w-4 h-4"/>
                                    </button>
                                </x-slot:append>
                            </x-badge>
                        </template>
                    </div>
                </label>
                <input class="hidden" wire:model="files" id="files" type="file" multiple x-bind:disabled="$wire.multiple"/>
            </div>
            <x-flux::editor wire:model="mailMessage.html_body" />
            <x-slot:footer>
                <div class="flex justify-end">
                    <div class="flex gap-1">
                        <x-button x-on:click="close()" class="mr-2">{{ __('Cancel') }}</x-button>
                        <x-button primary wire:click="send().then((success) => {if(success) close();})" class="ml-auto">{{ __('Send') }}</x-button>
                    </div>
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
</div>
