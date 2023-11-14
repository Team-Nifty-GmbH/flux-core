<div class="flex gap-6" x-data="{
                       ...folderTree(),
                       levels: $wire.entangle('folders'),
                       selectable: false,
                       select(event, level) {
                           if (level) {
                              $wire.set('folderId', level.id);
                              const current = document.querySelector('#mail-folders [selected]');
                              current?.classList.remove('bg-primary-500', 'text-white');
                              current?.removeAttribute('selected');

                              event.target.parentNode.classList.add('bg-primary-500', 'text-white');
                              event.target.parentNode.setAttribute('selected', true);
                           }
                       },
                       writeHtml() {
                            const html = $wire.mailMessage.html_body || $wire.mailMessage.text_body;
                            const host = document.getElementById('mail-body');
                            let shadow = host.shadowRoot;
                            if (!shadow) {
                                shadow = host.attachShadow({mode: 'open'});
                            }
                            document.createElement('div');
                            shadow.innerHTML = html;
                       },
                   }">
   <x-modal max-width="7xl" name="show-mail">
      <x-card class="flex flex-col gap-4">
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
               <x-button xs icon="paper-clip" x-on:click="$wire.download(file.id)" rounded>
                  <x-slot:label>
                     <span x-text="file.name"></span>
                  </x-slot:label>
               </x-button>
            </template>
         </div>
         <div class="p-4 border rounded-md overflow-auto" id="mail-body">
         </div>
      </x-card>
   </x-modal>
   <section class="max-w-[96rem] flex flex-col gap-4">
      <x-card id="mail-folders">
         <ul class="flex flex-col gap-1" wire:ignore>
            <template x-for="(level, i) in levels">
               <li x-html="renderLevel(level, i)"></li>
            </template>
         </ul>
      </x-card>
      <x-button x-show="$wire.mailAccounts" x-cloak spinner class="w-full" :label="__('Get new messages')" x-on:click="$wire.getNewMessages()" primary/>
   </section>
   <section class="grow" x-on:data-table-row-clicked="$wire.showMail($event.detail.id)">
      @include('tall-datatables::livewire.data-table')
   </section>
</div>
