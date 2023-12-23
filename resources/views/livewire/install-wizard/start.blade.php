<div>
    <div class="max-w-4xl mx-auto p-5">
        <div>
            <h1 class="text-2xl font-bold text-center text-gray-700 mb-4">Congratulations and Thank You for choosing Flux ERP!</h1>
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-700">Installation Steps:</h2>
                <p class="text-gray-600 mt-2">During the initial setup, essential data is established, which you can modify or augment later in the settings.</p>
            </div>

            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-700">Stay Updated:</h2>
                <p class="text-gray-600 mt-2">Be sure to keep your Flux ERP updated to benefit from the latest features and improvements.</p>
            </div>

            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-700">Feedback and Suggestions:</h2>
                <p class="text-gray-600 mt-2">Your experience with Flux ERP is important to us. We welcome your feedback and suggestions.</p>
            </div>
        </div>
    </div>
    @if($databaseConnectionSuccessful)
        <div class="mt-5 flex flex-col gap-1.5">
            <x-button x-cloak x-show="! $wire.requestRefresh" spinner primary wire:click="start(); resetProgress();" :label="__('Install')" />
            <x-button x-cloak x-show="$wire.requestRefresh" spinner primary wire:click="reload().then(() => window.location.reload(true));" :label="__('Reload')" />
        </div>
    @else
        <div class="flex flex-col gap-1.5">
            <x-errors/>
            <x-input wire:model="dbForm.host" :label="__('Database Host')" />
            <x-input wire:model="dbForm.port" :label="__('Database Port')" />
            <x-input wire:model="dbForm.database" :label="__('Database Name')" />
            <x-input wire:model="dbForm.username" :label="__('Database Username')" />
            <x-input wire:model="dbForm.password" :label="__('Database Password')" />
            <x-button primary wire:click="testDatabaseConnection()" spinner :label="__('Test Database Connection')" />
        </div>
    @endif
</div>
<div class="flex flex-col gap-4" x-cloak x-transition x-show="progress.started">
    <div>
        <div class="flex justify-between mb-1 items-center">
            <div class="flex gap-1.5 items-center">
                <span class="text-base font-medium text-blue-700 dark:text-white" x-text="progress.title"></span>
                <div role="status" x-cloak x-show="progress.started && progress.progress < 100">
                    <svg aria-hidden="true" class="w-4 h-4 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                        <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                    </svg>
                </div>
            </div>
            <span x-ref="progress" class="text-sm font-medium text-blue-700 dark:text-white" x-text="progress.progress + '%'"></span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
            <div x-ref="progressBar" class="transition-all bg-blue-600 h-2.5 rounded-full" x-bind:style="'width:' + progress.progress + '%'" style="width: 0"></div>
        </div>
    </div>
    <pre class="max-h-96 p-1 font-mono bg-black text-white rounded-md overflow-auto">
        <template x-for="(line, index) in progress.message">
            <div class="flex">
                <span class="text-gray-300 w-8" x-text="index"></span>
                <span x-html="line"></span>
            </div>
        </template>
        <div x-ref="progressMessage"></div>
    </pre>
</div>
