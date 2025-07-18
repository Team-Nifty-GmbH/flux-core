<div
    class="flex flex-col-reverse gap-6 sm:flex-row"
    x-data="{
        writeHtml() {
            const host = document.getElementById('mail-body')
            let shadow = host.shadowRoot
            if (! shadow) {
                shadow = host.attachShadow({ mode: 'open' })
            }
            document.createElement('div')
            shadow.innerHTML = $wire.mailMessage.html_body

            if (
                shadow.innerHTML !== $wire.mailMessage.html_body &&
                $wire.mailMessage.text_body
            ) {
                shadow.innerHTML = $wire.mailMessage.text_body
            }
        },
    }"
>
    <x-modal size="7xl" id="show-mail">
        <div class="flex flex-col gap-2">
            <div class="flex">
                <div class="grow">
                    <div
                        class="font-semibold"
                        x-text="$wire.mailMessage.from"
                    ></div>
                    <div
                        class="text-sm"
                        x-text="$wire.mailMessage.subject"
                    ></div>
                </div>
                <div class="text-right">
                    <div
                        class="font-semibold"
                        x-text="window.formatters.datetime($wire.mailMessage.date)"
                    ></div>
                    <div
                        class="text-sm"
                        x-text="$wire.mailMessage.slug"
                    ></div>
                    <div class="flex justify-end">
                        <x-dropdown position="bottom-start">
                            <x-slot:action>
                                <x-button
                                    icon="chevron-down"
                                    x-on:click="show = !show"
                                    sm
                                    color="secondary"
                                    :text="__('Actions')"
                                />
                            </x-slot>
                            @canAction(\FluxErp\Actions\Ticket\CreateTicket::class)
                                <x-dropdown.items
                                    :text="__('Create ticket')"
                                    wire:click="createTicket($wire.mailMessage.id)"
                                />
                            @endcanAction

                            @canAction(\FluxErp\Actions\Lead\CreateLead::class)
                                <x-dropdown.items
                                    :text="__('Create lead')"
                                    wire:click="createLead($wire.mailMessage.id)"
                                />
                            @endcanAction

                            @canAction(\FluxErp\Actions\PurchaseInvoice\CreatePurchaseInvoice::class)
                                <x-dropdown.items
                                    :text="__('Create purchase invoice')"
                                    wire:click="createPurchaseInvoice($wire.mailMessage.id)"
                                />
                            @endcanAction
                        </x-dropdown>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <div class="text-sm">{{ __('To') }}:</div>
                <template x-for="to in $wire.mailMessage.to">
                    <span
                        x-html="window.formatters.badge(to.full, 'neutral')"
                    ></span>
                </template>
            </div>
            <div
                class="flex items-center gap-1"
                x-cloak
                x-show="$wire.mailMessage.bcc.length"
            >
                <div class="text-sm">{{ __('CC') }}:</div>
                <template x-for="cc in $wire.mailMessage.cc">
                    <span
                        x-html="window.formatters.badge(cc.full, 'neutral')"
                    ></span>
                </template>
            </div>
            <div
                class="flex items-center gap-1"
                x-cloak
                x-show="$wire.mailMessage.bcc.length"
            >
                <div class="text-sm">{{ __('BCC') }}:</div>
                <template x-for="bcc in $wire.mailMessage.bcc">
                    <span
                        x-html="window.formatters.badge(bcc.full, 'neutral')"
                    ></span>
                </template>
            </div>
            <div class="flex gap-1">
                <template
                    x-for="communicatable in $wire.mailMessage.communicatables"
                >
                    <x-button
                        color="secondary"
                        sm
                        light
                        icon="link"
                        rounded
                        href="#"
                        x-bind:href="communicatable.href"
                        wire:navigate
                    >
                        <x-slot:text>
                            <span x-text="communicatable.label"></span>
                        </x-slot>
                    </x-button>
                </template>
            </div>
            <div class="flex gap-1">
                <template x-for="file in $wire.mailMessage.attachments">
                    <x-button
                        color="secondary"
                        sm
                        light
                        icon="paper-clip"
                        x-on:click="$wire.download(file.id)"
                        rounded
                    >
                        <x-slot:text>
                            <span x-text="file.name"></span>
                        </x-slot>
                    </x-button>
                </template>
            </div>
            <div
                class="overflow-auto rounded-md border p-4"
                id="mail-body"
            ></div>
        </div>
    </x-modal>
    <section class="flex max-w-[96rem] flex-col gap-4">
        <x-card
            id="mail-folders"
            x-on:folder-tree-select="$wire.set('folderId', $event.detail.id, true)"
        >
            <x-flux::checkbox-tree
                tree="$wire.folders"
                name-attribute="name"
                :with-search="true"
            >
                <x-slot:afterTree>
                    <div class="pt-4">
                        <x-button
                            x-show="$wire.mailAccounts"
                            x-cloak
                            spinner="getNewMessages()"
                            class="w-full"
                            :text="__('Get new messages')"
                            x-on:click="$wire.getNewMessages()"
                            color="indigo"
                        />
                    </div>
                </x-slot>
            </x-flux::checkbox-tree>
        </x-card>
    </section>
    <section
        class="grow"
        x-on:data-table-row-clicked="$wire.showMail($event.detail.id)"
    >
        @include('tall-datatables::livewire.data-table')
    </section>
</div>
