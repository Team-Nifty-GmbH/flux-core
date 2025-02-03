<x-modal name="edit-mail-folders">
    <x-card class="bg-gray-50">
        <div class="grid grid-cols-2 gap-4">
            <x-card id="mail-folders" x-on:folder-tree-select="$wire.editMailFolder($event.detail.id)">
                <x-flux::checkbox-tree
                    tree="$wire.folders"
                    name-attribute="name"
                    children-attribute="children"
                >
                    <x-slot:afterTree>
                        <x-button spinner="syncFolders" class="w-full" primary :label="__('Sync folders')" wire:click="syncFolders($wire.mailAccount.id)"/>
                    </x-slot:afterTree>
                </x-flux::checkbox-tree>
            </x-card>
            <div x-show="$wire.mailFolder.id" x-transition>
                <x-card>
                    <div class="flex flex-col gap-1.5">
                        <x-input wire:model="mailFolder.name" :label="__('Name')" :disabled="true" />
                        <x-toggle wire:model="mailFolder.can_create_purchase_invoice" :label="__('Can Create Purchase Invoice')" />
                        <x-toggle wire:model="mailFolder.can_create_ticket" :label="__('Can Create Ticket')" />
                        <x-toggle wire:model="mailFolder.is_active" :label="__('Active')" />
                    </div>
                    <x-slot:footer>
                        <div class="flex w-full justify-end">
                            <x-button spinner="saveMailFolder" primary :label="__('Save')" wire:click="saveMailFolder()"/>
                        </div>
                    </x-slot:footer>
                </x-card>
            </div>
        </div>
        <x-slot:footer>
            <div class="flex w-full justify-end">
                <x-button :label="__('Close')" x-on:click="close()" />
            </div>
        </x-slot:footer>
    </x-card>
</x-modal>
<x-modal name="edit-mail-account">
    <x-card footer-classes="flex justify-end gap-1.5">
        <x-slot:title>
            {{ __('Edit Mail Account') }}
        </x-slot:title>
        <div class="flex flex-col gap-4">
            <x-card :title="__('IMAP Settings')" footer-classes="flex justify-end gap-1.5">
                <div class="flex flex-col gap-4">
                    <x-select :label="__('Protocol')" wire:model="mailAccount.protocol">
                        <x-select.option value="imap">{{ __('IMAP') }}</x-select.option>
                        <x-select.option value="pop3">{{ __('POP3') }}</x-select.option>
                        <x-select.option value="nntp">{{ __('NNTP') }}</x-select.option>
                    </x-select>
                    <x-input x-bind:disabled="$wire.mailAccount.id" wire:model="mailAccount.email" :label="__('Email')" />
                    <x-inputs.password wire:model="mailAccount.password" :label="__('Password')" />
                    <x-input wire:model="mailAccount.host" :label="__('Host')" />
                    <x-inputs.number wire:model="mailAccount.port" :label="__('Port')" />
                    <x-select :label="__('Encryption')" wire:model="mailAccount.encryption">
                        <x-select.option value="ssl">{{ __('SSL') }}</x-select.option>
                        <x-select.option value="tls">{{ __('TLS') }}</x-select.option>
                    </x-select>
                    <x-checkbox wire:model.boolean="mailAccount.has_valid_certificate" :label="__('Validate Certificate')" />
                    <x-checkbox wire:model.boolean="mailAccount.is_o_auth" :label="__('oAuth')" />
                    <x-toggle wire:model.boolean="mailAccount.is_auto_assign" :label="__('Auto assign mails')" />
                </div>
                <x-slot:footer>
                    <x-button spinner primary :label="__('Test Connection')" x-on:click="$wire.testImapConnection()"/>
                </x-slot:footer>
            </x-card>
            <x-card :title="__('SMTP Settings')" footer-classes="flex justify-end gap-1.5">
                <div class="flex flex-col gap-4">
                    <x-input wire:model="mailAccount.smtp_email" :label="__('Email')" />
                    <x-inputs.password wire:model="mailAccount.smtp_password" :label="__('Password')" />
                    <x-input wire:model="mailAccount.smtp_host" :label="__('Host')" />
                    <x-inputs.number wire:model="mailAccount.smtp_port" :label="__('Port')" />
                    <x-select :label="__('Encryption')" wire:model="mailAccount.smtp_encryption">
                        <x-select.option value="ssl">{{ __('SSL') }}</x-select.option>
                        <x-select.option value="tls">{{ __('TLS') }}</x-select.option>
                    </x-select>
                </div>
                <x-slot:footer>
                    <x-button
                        spinner
                        :label="__('Send test mail')"
                        wire:flux-confirm.prompt="{{  __('Send test mail to') }}||{{  __('Cancel') }}|{{  __('Send') }}"
                        wire:click="sendTestMail($promptValue())"
                    />
                    <x-button spinner primary :label="__('Test Connection')" x-on:click="$wire.testSmtpConnection()"/>
                </x-slot:footer>
            </x-card>
        </div>
        <x-slot:footer>
            <x-button flat :label="__('Cancel')" x-on:click="close"/>
            <x-button primary :label="__('Save')" x-on:click="$wire.save().then((success) => {if(success) {close();}})"/>
        </x-slot:footer>
    </x-card>
</x-modal>
