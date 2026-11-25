<div class="flex h-full flex-col gap-4 p-4">
    <div>
        <h2
            class="truncate text-lg font-semibold text-gray-700 dark:text-gray-400"
        >
            {{ __('Personnel files') }}
        </h2>
        <hr class="mt-2" />
    </div>
    <div class="flex flex-col gap-4 overflow-auto">
        <table class="w-full">
            <thead>
                <tr>
                    <th
                        colspan="2"
                        class="pb-1 text-left text-sm font-medium text-gray-500"
                    >
                        {{ __('Missing required documents') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($missingDocuments as $employee)
                    <tr class="border-t border-gray-100 dark:border-gray-700">
                        <td class="py-1.5 text-sm">
                            <a
                                href="{{ route('human-resources.employees.id', ['id' => $employee['id']]) }}"
                                wire:navigate
                            >
                                {{ $employee['name'] }}
                            </a>
                        </td>
                        <td class="py-1.5 text-right text-sm text-gray-500">
                            {{ implode(', ', $employee['document_types']) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="py-2 text-center text-gray-400">
                            {{ __('All personnel files are complete') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <table class="w-full">
            <thead>
                <tr>
                    <th
                        colspan="2"
                        class="pb-1 text-left text-sm font-medium text-gray-500"
                    >
                        {{ __('Retention ended') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($expiredDocuments as $employee)
                    <tr class="border-t border-gray-100 dark:border-gray-700">
                        <td class="py-1.5 text-sm">
                            <a
                                href="{{ route('human-resources.employees.id', ['id' => $employee['id']]) }}"
                                wire:navigate
                            >
                                {{ $employee['name'] }}
                            </a>
                        </td>
                        <td class="py-1.5 text-right">
                            <x-badge
                                color="amber"
                                :text="(string) $employee['count']"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="py-2 text-center text-gray-400">
                            {{ __('No documents past their retention') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
