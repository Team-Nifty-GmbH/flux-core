<div
    x-data="{
        get isMultiGroup() {
            return ($wire.groupKeys?.length ?? 0) > 1
        },
        get isLastGroup() {
            return ($wire.currentGroupIndex ?? 0) >= ($wire.groupKeys?.length ?? 0) - 1
        },
        get isFirstGroup() {
            return ($wire.currentGroupIndex ?? 0) <= 0
        },
    }"
>
    <x-modal
        size="7xl"
        id="edit-mail"
        x-on:close.self="$wire.clear()"
        persistent
    >
        <x-slot:title>{{ __('Email') }}</x-slot>
        <div class="flex flex-col gap-2">
            <div x-cloak x-show="isMultiGroup" class="flex flex-col gap-2">
                <x-alert
                    color="amber"
                    :text="__('All emails will be sent after the last group')"
                />
                <div class="flex items-center gap-2 text-sm">
                    <x-badge color="indigo">
                        <x-slot:text>
                            <span x-text="$wire.currentGroupLabel"></span>
                        </x-slot>
                    </x-badge>
                    <span class="text-gray-500">
                        <span x-text="$wire.currentGroupRecipientCount"></span> {{ __('recipient(s)') }}
                    </span>
                    <span class="text-gray-400">·</span>
                    <span
                        class="text-gray-500"
                        x-text="$wire.groupKeys?.length > 0
                            ? '{{ __('Group') }} ' + (($wire.currentGroupIndex ?? 0) + 1) + '/' + $wire.groupKeys.length
                            : '-'"
                    ></span>
                </div>
            </div>

            @php
                $receiverSearchRequest = [
                    'url' => route('search', \FluxErp\Models\Address::class),
                    'method' => 'post',
                    'params' => [
                        'searchFields' => ['email_primary', 'name'],
                        'fields' => ['email_primary'],
                        'mapping' => ['value' => 'email_primary', 'description' => 'label'],
                        'whereNotNull' => ['email_primary'],
                    ],
                ];
            @endphp

            <x-flux::pillbox
                wire:model="mailMessage.to"
                class="flex flex-col gap-1.5"
                x-bind:class="{ 'pointer-events-none opacity-50': $wire.multiple }"
                :label="__('To')"
                :placeholder="__('Add a new to')"
                lazy="2"
                :request="$receiverSearchRequest"
            />
            <x-flux::pillbox
                wire:model="mailMessage.cc"
                class="flex flex-col gap-1.5"
                :label="__('CC')"
                :placeholder="__('Add a new cc')"
                lazy="2"
                :request="$receiverSearchRequest"
            />
            <x-flux::pillbox
                wire:model="mailMessage.bcc"
                class="flex flex-col gap-1.5"
                :label="__('BCC')"
                :placeholder="__('Add a new bcc')"
                lazy="2"
                :request="$receiverSearchRequest"
            />
            <div class="grow">
                <x-input
                    wire:model="mailMessage.subject"
                    class="w-full"
                    :label="__('Subject')"
                />
            </div>
            <hr />
            <div class="flex-1" id="email-template">
                <x-select.styled
                    wire:model.live="selectedTemplateId"
                    wire:change="applyTemplate"
                    :label="__('Email Template')"
                    select="label:name|value:id"
                    :options="[]"
                />
            </div>
            <x-select.styled
                required
                wire:model="mailMessage.mail_account_id"
                :label="__('Send From')"
                select="label:name|value:id"
                :options="$mailAccounts"
            />
            <div>
                <x-label :label="__('Attachments')" />
                <label for="files">
                    <div
                        class="flex min-h-8 w-full gap-1 rounded-md bg-gray-100 p-1.5"
                    >
                        <template x-for="file in $wire.mailMessage.attachments">
                            <x-badge white rounded>
                                <x-slot:left>
                                    <x-icon name="paper-clip" class="h-4 w-4" />
                                </x-slot>
                                <x-slot:text>
                                    <div
                                        x-on:click.prevent="file.id && $wire.download(file.id)"
                                        x-bind:class="{ 'cursor-pointer': file.id }"
                                    >
                                        <span x-text="file.name"></span>
                                    </div>
                                </x-slot>
                                <x-slot:right>
                                    <button
                                        type="button"
                                        x-on:click.prevent="
                                            $wire.mailMessage.attachments.splice(
                                                $wire.mailMessage.attachments.indexOf(file),
                                                1,
                                            )
                                        "
                                    >
                                        <x-icon name="x-mark" class="h-4 w-4" />
                                    </button>
                                </x-slot>
                            </x-badge>
                        </template>
                    </div>
                </label>
                <input
                    class="hidden"
                    wire:model="files"
                    id="files"
                    type="file"
                    multiple
                    x-bind:disabled="$wire.multiple"
                />
            </div>
            <x-flux::editor wire:model="mailMessage.html_body" scope="mail" />
        </div>
        <x-slot:footer>
            <x-button
                x-cloak
                x-show="! isMultiGroup"
                color="secondary"
                light
                x-on:click="$tsui.close.modal('edit-mail')"
                class="mr-2"
                :text="__('Cancel')"
            />
            <x-button
                x-cloak
                x-show="isMultiGroup"
                color="secondary"
                light
                wire:click="cancelMultiGroup()"
                class="mr-2"
                :text="__('Cancel')"
            />
            <x-button
                x-cloak
                x-show="isMultiGroup && ! isFirstGroup"
                color="secondary"
                loading="previousGroup"
                wire:click="previousGroup()"
                :text="__('Back')"
            />
            <x-button
                x-cloak
                x-show="isMultiGroup && ! isLastGroup"
                color="indigo"
                loading="nextGroup"
                wire:click="nextGroup()"
                class="ml-auto"
                :text="__('Continue')"
            />
            @stack('edit-mail-modal-footer')
            <x-button
                x-cloak
                x-show="! isMultiGroup || isLastGroup"
                color="indigo"
                loading="send"
                x-on:click="$wire.send().then((success) => {if(success) $tsui.close.modal('edit-mail');})"
                class="ml-auto"
                :text="__('Send')"
            />
        </x-slot>
    </x-modal>
</div>
