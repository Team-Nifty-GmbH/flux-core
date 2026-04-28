<div class="flex h-full flex-col gap-4 p-4">
    <div>
        <h2
            class="truncate text-lg font-semibold text-gray-700 dark:text-gray-400"
        >
            {{ __('Upcoming Birthdays') }}
        </h2>
        <hr class="mt-2" />
    </div>
    <div class="overflow-auto">
        <table class="w-full">
            <tbody>
                @forelse($birthdays as $birthday)
                    <tr class="border-t border-gray-100 dark:border-gray-700">
                        <td class="py-1.5 text-sm">{{ $birthday['name'] }}</td>
                        <td class="py-1.5 text-right text-sm text-gray-500">
                            {{ $birthday['date'] }}
                        </td>
                        <td class="py-1.5 text-right">
                            <x-badge
                                color="primary"
                                :text="(string) $birthday['age']"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-4 text-center text-gray-400">
                            {{ __('No upcoming birthdays') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
