<div>
    <div class="space-y-8 divide-y divide-gray-200">
        <div class="space-y-8 divide-y divide-gray-200">
            <div x-data="{logo: @entangle('logo')}">
                <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-6">
                        {{__('Logo')}}
                    </div>
                    <div class="sm:col-span-6">
                        <div>
                            <div x-data="{
                                isLogoDropping: false,
                                isLogoUploading: false,
                                logoProgress: 0,
                                handleLogoSelect(event) {
                                    if (!event.target.multiple) {
                                        this.uploadLogo(event.target.files[0])
                                    }
                                },
                                handleLogoDrop(event) {
                                    if (event.dataTransfer.files.length === 1) {
                                        this.uploadLogo(event.dataTransfer.files[0])
                                    }
                                },
                                uploadLogo(file) {
                                    const $this = this;
                                    this.isLogoUploading = true
                                    @this.upload('logo', file,
                                        function (success) {
                                            $this.isLogoUploading = false
                                            $this.logoProgress = 0
                                        },
                                        function(error) {
                                            console.log('error', error)
                                        },
                                        function (event) {
                                            $this.logoProgress = event.detail.progress
                                        }
                                    )
                                }
                            }">
                                <div class="relative flex flex-col items-center justify-center"
                                     x-on:drop="isLogoDropping = false"
                                     x-on:drop.prevent="handleLogoDrop($event)"
                                     x-on:dragover.prevent="isLogoDropping = true"
                                     x-on:dragleave.prevent="isLogoDropping = false"
                                >
                                    <div class="flexjustify-center absolute top-0 bottom-0 left-0 right-0 z-30 items-center bg-blue-500 opacity-90"
                                         x-show="isLogoDropping"
                                    >
                                        <span class="text-3xl text-white">{{ __('Release file to upload!') }}</span>
                                    </div>
                                    <label class="order-2 flex w-full cursor-pointer select-none flex-col items-center justify-center rounded-md border-dashed border-gray-300 bg-gray-50 p-10 shadow hover:bg-slate-50"
                                           for="logo-upload"
                                    >
                                        <div class="pb-3">
                                            <x-heroicons name="arrow-up-on-square" class="h-12 w-12" />
                                        </div>
                                        <p>{{ __('Click here to select a file to upload') }}</p>
                                        <em class="italic text-slate-400">{{ __('(Or drag a file to the page)') }}</em>
                                        <div class="mt-3 h-[2px] w-1/2 bg-gray-200">
                                            <div
                                                class="h-[2px] bg-blue-500"
                                                style="transition: width 1s"
                                                :style="`width: ${logoProgress}%;`"
                                                x-show="isLogoUploading"
                                            >
                                            </div>
                                        </div>

                                    </label>
                                    <input type="file" id="logo-upload" accept="image/*" wire:model="logo" class="hidden"
                                           @change="handleLogoSelect"/>
                                </div>
                            </div>
                        </div>
                        @if($logo)
                            <div class="space-y-3">
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex w-0 flex-1 items-center">
                                        <x-icon name="paper-clip" class="h-4 w-4"/>
                                        <span
                                            class="w-0 flex-1 truncate pl-1">{{ $logo[0]?->getClientOriginalName() }}</span>
                                    </div>
                                    <div class="flex flex-shrink-0 space-x-4">
                                        <x-button negative wire:click="removeUpload('logo')" :label="__('Delete')"/>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    @if ($logoImage && ! $logo)
                        {!! $logoImage !!}
                    @elseif ($logo)
                        <div class="sm:col-span-6">
                            <img class="max-h-32 object-contain" src="{{ $logo[0]->temporaryUrl() }}">
                        </div>
                    @endif
                    <div class="sm:col-span-6">
                        {{__('Logo small')}}
                    </div>
                    <div class="sm:col-span-6">
                        <div>
                            <div x-data="{
                                isLogoSmallDropping: false,
                                isLogoSmallUploading: false,
                                logoSmallProgress: 0,
                                handleLogoSmallSelect(event) {
                                    if (!event.target.multiple) {
                                        this.uploadLogoSmall(event.target.files[0])
                                    }
                                },
                                handleLogoSmallDrop(event) {
                                    if (event.dataTransfer.files.length === 1) {
                                        this.uploadLogoSmall(event.dataTransfer.files[0])
                                    }
                                },
                                uploadLogoSmall(file) {
                                    const $this = this;
                                    this.isLogoSmallUploading = true
                                    @this.upload('logoSmall', file,
                                        function (success) {
                                            $this.isLogoSmallUploading = false
                                            $this.logoSmallProgress = 0
                                        },
                                        function(error) {
                                            console.log('error', error)
                                        },
                                        function (event) {
                                            $this.logoSmallProgress = event.detail.progress
                                        }
                                    )
                                }
                            }">
                                <div class="relative flex flex-col items-center justify-center"
                                     x-on:drop="isLogoSmallDropping = false"
                                     x-on:drop.prevent="handleLogoSmallDrop($event)"
                                     x-on:dragover.prevent="isLogoSmallDropping = true"
                                     x-on:dragleave.prevent="isLogoSmallDropping = false"
                                >
                                    <div
                                        class="absolute top-0 bottom-0 left-0 right-0 z-30 flex items-center justify-center bg-blue-500 opacity-90"
                                        x-show="isLogoSmallDropping"
                                    >
                                        <span class="text-3xl text-white">{{ __('Release file to upload!') }}</span>
                                    </div>
                                    <label
                                        class="order-2 flex w-full cursor-pointer select-none flex-col items-center justify-center rounded-md border-dashed border-gray-300 bg-gray-50 p-10 shadow hover:bg-slate-50"
                                        for="logo-small-upload"
                                    >
                                        <div class="pb-3">
                                            <x-heroicons name="arrow-up-on-square" class="h-12 w-12" />
                                        </div>
                                        <p>{{ __('Click here to select a file to upload') }}</p>
                                        <em class="italic text-slate-400">{{ __('(Or drag a file to the page)') }}</em>
                                        <div class="mt-3 h-[2px] w-1/2 bg-gray-200">
                                            <div
                                                class="h-[2px] bg-blue-500"
                                                style="transition: width 1s"
                                                :style="`width: ${logoSmallProgress}%;`"
                                                x-show="isLogoSmallUploading"
                                            >
                                            </div>
                                        </div>

                                    </label>
                                    <input type="file" id="logo-small-upload" wire:model="logoSmall" accept="image/*" class="hidden"
                                           @change="handleLogoSmallSelect"/>
                                </div>
                            </div>
                        </div>
                        @if($logoSmall)
                            <div class="max-h-32 space-y-3">
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex w-0 flex-1 items-center">
                                        <x-icon name="paper-clip" class="h-4 w-4"/>
                                        <span
                                            class="w-0 flex-1 truncate pl-1">{{ $logoSmall[0]?->getClientOriginalName() }}</span>
                                    </div>
                                    <div class="flex flex-shrink-0 space-x-4">
                                        <x-button negative wire:click="removeUpload('logoSmall')"
                                                  :label="__('Delete')"/>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    @if ($logoSmallImage && ! $logoSmall)
                        {!! $logoSmallImage !!}
                    @elseif ($logoSmall)
                        <div class="sm:col-span-6">
                            <img class="max-h-32 object-contain" src="{{ $logoSmall[0]->temporaryUrl() }}">
                        </div>
                    @endif
                </div>
            </div>
            <x-errors/>
        </div>
    </div>
</div>
