<div class="flex flex-col-reverse sm:flex-row gap-6"
     x-data="{
        writeHtml() {
            const host = document.getElementById('mail-body');
            let shadow = host.shadowRoot;
            if (!shadow) {
                shadow = host.attachShadow({mode: 'open'});
            }
            document.createElement('div');
            shadow.innerHTML = $wire.mailMessage.html_body;

            if (shadow.innerHTML !== $wire.mailMessage.html_body && $wire.mailMessage.text_body) {
                shadow.innerHTML = $wire.mailMessage.text_body;
            }
        }
    }"
>
   <x-modal size="7xl" id="show-mail" class="flex flex-col gap-4">
        <div class="flex">
            <div class="grow">
               <div class="font-semibold" x-text="$wire.mailMessage.from"></div>
               <div class="text-sm" x-text="$wire.mailMessage.subject"></div>
            </div>
            <div class="text-right">
               <div class="font-semibold" x-text="window.formatters.datetime($wire.mailMessage.date)"></div>
               <div class="text-sm" x-text="$wire.mailMessage.slug"></div>
            </div>
        </div>
        <div class="flex gap-1 items-center">
            <div class="text-sm">{{ __('To') }}: </div>
                <template x-for="to in $wire.mailMessage.to">
                   <span x-html="window.formatters.badge(to.full, 'neutral')"></span>
                </template>
        </div>
        <div class="flex gap-1 items-center" x-cloak x-show="$wire.mailMessage.bcc.length">
            <div class="text-sm">{{ __('CC') }}: </div>
            <template x-for="cc in $wire.mailMessage.cc">
               <span x-html="window.formatters.badge(cc.full, 'neutral')"></span>
            </template>
        </div>
        <div class="flex gap-1 items-center" x-cloak x-show="$wire.mailMessage.bcc.length">
            <div class="text-sm">{{ __('BCC') }}: </div>
            <template x-for="bcc in $wire.mailMessage.bcc">
               <span x-html="window.formatters.badge(bcc.full, 'neutral')"></span>
            </template>
        </div>
        <div class="flex gap-1">
            <template x-for="file in $wire.mailMessage.attachments">
               <x-button color="secondary" light xs icon="paper-clip" x-on:click="$wire.download(file.id)" rounded>
                  <x-slot:label>
                     <span x-text="file.name"></span>
                  </x-slot:label>
               </x-button>
            </template>
        </div>
        <div class="p-4 border rounded-md overflow-auto" id="mail-body">
        </div>
   </x-modal>
   <section class="max-w-[96rem] flex flex-col gap-4">
       <x-card id="mail-folders" x-on:folder-tree-select="$wire.set('folderId', $event.detail.id, true)">
           <x-flux::checkbox-tree
               tree="$wire.folders"
               name-attribute="name"
               :with-search="true"
           >
               <x-slot:afterTree>
                   <div class="pt-4">
                       <x-button x-show="$wire.mailAccounts" x-cloak spinner="getNewMessages()" class="w-full" :text="__('Get new messages')" x-on:click="$wire.getNewMessages()" color="indigo"/>
                   </div>
               </x-slot:afterTree>
           </x-flux::checkbox-tree>
       </x-card>
   </section>
   <section class="grow" x-on:data-table-row-clicked="$wire.showMail($event.detail.id)">
      @include('tall-datatables::livewire.data-table')
   </section>
</div>
