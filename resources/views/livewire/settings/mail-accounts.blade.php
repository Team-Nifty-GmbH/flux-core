<x-modal id="edit-mail-folders" class="bg-gray-50">
    <div class="grid grid-cols-2 gap-1.5">
        <x-card
            id="mail-folders"
            x-on:folder-tree-select="$wire.editMailFolder($event.detail.id)"
        >
            <x-flux::checkbox-tree
                tree="$wire.folders"
                name-attribute="name"
                children-attribute="children"
            >
                <x-slot:afterTree>
                    <x-button
                        loading="syncFolders"
                        class="w-full"
                        color="indigo"
                        :text="__('Sync folders')"
                        wire:click="syncFolders($wire.mailAccount.id)"
                    />
                </x-slot>
            </x-flux::checkbox-tree>
        </x-card>
        <div x-show="$wire.mailFolder.id" x-transition>
            <x-card>
                <div class="flex flex-col gap-1.5">
                    <x-input
                        wire:model="mailFolder.name"
                        :label="__('Name')"
                        :disabled="true"
                    />
                    <x-toggle
                        wire:model="mailFolder.can_create_purchase_invoice"
                        :label="__('Can Create Purchase Invoice')"
                    />
                    <x-toggle
                        wire:model="mailFolder.can_create_ticket"
                        :label="__('Can Create Ticket')"
                    />
                    <x-toggle
                        wire:model="mailFolder.can_create_lead"
                        :label="__('Can Create Lead')"
                    />
                    <x-toggle
                        wire:model="mailFolder.is_active"
                        :label="__('Active')"
                    />
                </div>
                <x-slot:footer>
                    <x-button
                        loading="saveMailFolder"
                        color="indigo"
                        :text="__('Save')"
                        wire:click="saveMailFolder()"
                    />
                </x-slot>
            </x-card>
        </div>
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            :text="__('Close')"
            x-on:click="$modalClose('edit-mail-folders')"
        />
    </x-slot>
</x-modal>
<x-modal id="edit-mail-account">
    <x-slot:title>
        {{ __('Edit Mail Account') }}
    </x-slot>
    <div class="flex flex-col gap-1.5">
        <x-card
            :header="__('IMAP Settings')"
            footer-classes="flex justify-end gap-1.5"
        >
            <div class="flex flex-col gap-1.5">
                <x-select.styled
                    :label="__('Protocol')"
                    wire:model="mailAccount.protocol"
                    :options="[
                        ['value' => 'imap', 'label' => __('IMAP')],
                        ['value' => 'pop3', 'label' => __('POP3')],
                        ['value' => 'nntp', 'label' => __('NNTP')],
                    ]"
                />
                <x-input
                    x-bind:disabled="$wire.mailAccount.id"
                    wire:model="mailAccount.email"
                    :label="__('Email')"
                />
                <x-password
                    wire:model="mailAccount.password"
                    :label="__('Password')"
                />
                <x-input wire:model="mailAccount.host" :label="__('Host')" />
                <x-number wire:model="mailAccount.port" :label="__('Port')" />
                <x-select.styled
                    :label="__('Encryption')"
                    wire:model="mailAccount.encryption"
                    :options="[
                        ['value' => 'ssl', 'label' => __('SSL')],
                        ['value' => 'tls', 'label' => __('TLS')],
                    ]"
                />
                <x-checkbox
                    wire:model.boolean="mailAccount.has_valid_certificate"
                    :label="__('Validate Certificate')"
                />
                <x-checkbox
                    wire:model.boolean="mailAccount.is_o_auth"
                    :label="__('oAuth')"
                />
                <x-toggle
                    wire:model.boolean="mailAccount.is_auto_assign"
                    :label="__('Auto assign mails')"
                />
            </div>
            <x-slot:footer>
                <x-button
                    loading
                    color="indigo"
                    :text="__('Test Connection')"
                    x-on:click="$wire.testImapConnection()"
                />
            </x-slot>
        </x-card>
        <x-card
            :header="__('SMTP Settings')"
            footer-classes="flex justify-end gap-1.5"
        >
            <div class="flex flex-col gap-1.5">
                <x-input
                    wire:model="mailAccount.smtp_email"
                    :label="__('Email')"
                />
                <x-password
                    wire:model="mailAccount.smtp_password"
                    :label="__('Password')"
                />
                <x-input
                    wire:model="mailAccount.smtp_host"
                    :label="__('Host')"
                />
                <x-number
                    wire:model="mailAccount.smtp_port"
                    :label="__('Port')"
                />
                <x-select.styled
                    :label="__('Encryption')"
                    wire:model="mailAccount.smtp_encryption"
                    :options="[
                        ['value' => 'ssl', 'label' => __('SSL')],
                        ['value' => 'tls', 'label' => __('TLS')],
                    ]"
                />
            </div>
            <x-slot:footer>
                <x-button
                    color="secondary"
                    light
                    spinner
                    :text="__('Send test mail')"
                    wire:flux-confirm.prompt="{{  __('Send test mail to') }}||{{  __('Cancel') }}|{{  __('Send') }}"
                    wire:click="sendTestMail($promptValue())"
                />
                <x-button
                    loading
                    color="indigo"
                    :text="__('Test Connection')"
                    x-on:click="$wire.testSmtpConnection()"
                />
            </x-slot>
        </x-card>
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose('edit-mail-account')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            x-on:click="$wire.save().then((success) => {if(success) {$modalClose('edit-mail-account');}})"
        />
    </x-slot>
</x-modal>
