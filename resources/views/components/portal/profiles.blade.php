<div>
    <div>
        <div class="flex w-full flex-col-reverse justify-between md:flex-row">
            <h2 class="pt-5 text-base font-bold uppercase md:pt-0">
                {{ __('Edit profiles') }}
            </h2>
        </div>
        <h1 class="pt-5 text-5xl font-bold">
            {{ __('Manage users') }}
        </h1>
        <div class="flex w-full justify-end pt-8">
            <x-button primary :label="__('New user')" :href="route('portal.profile.id?', 'new')" />
        </div>
    </div>
    <div
        class="pt-8"
        x-data="{
            addresses: $wire.entangle('addresses').defer,
            editUserUrl: '{{ route('portal.profile.id?', ['id' => ':addressId']) }}'
            }"
    >
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
            <tr class="divide-x divide-gray-200">
                <th scope="col"
                    class="py-3.5 pl-4 pr-4 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                    {{ __('Firstname') }}
                </th>
                <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">
                    {{ __('Lastname') }}
                </th>
                <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">
                    {{ __('Email') }}
                </th>
                <th scope="col" class="py-2 pl-2 pr-2 text-left text-sm font-semibold text-gray-900">
                </th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
            <template x-for="address in addresses">
                <tr class="divide-x divide-gray-200">
                    <td x-text="address.firstname" class="whitespace-nowrap py-4 pl-4 pr-4 text-sm font-medium text-gray-900 sm:pl-6" />
                    <td x-text="address.lastname" class="whitespace-nowrap py-4 pl-4 pr-4 text-sm font-medium text-gray-900 sm:pl-6" />
                    <td x-text="address.login_name" class="whitespace-nowrap py-4 pl-4 pr-4 text-sm font-medium text-gray-900 sm:pl-6" />
                    <td class="whitespace-nowrap py-2 pl-2 pr-2 text-center text-sm text-gray-500">
                        <x-button :label="__('Edit')" href="#" x-bind:href="editUserUrl.replace(':addressId', address.id)" />
                    </td>
                </tr>
            </template>
            </tbody>
    </div>
</div>
