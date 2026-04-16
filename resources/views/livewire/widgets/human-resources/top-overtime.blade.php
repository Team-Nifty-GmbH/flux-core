<div class="flex h-full flex-col gap-4 p-4">
    <div>
        <h2
            class="truncate text-lg font-semibold text-gray-700 dark:text-gray-400"
        >
            {{ __('Top Overtime') }}
        </h2>
        <hr class="mt-2" />
    </div>
    <div class="overflow-auto">
        <table class="w-full">
            <thead>
                <tr
                    class="text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                >
                    <th class="pb-2">#</th>
                    <th class="pb-2">{{ __('Name') }}</th>
                    <th class="pb-2">{{ __('Department') }}</th>
                    <th class="pb-2 text-right">{{ __('Hours') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    <tr class="border-t border-gray-100 dark:border-gray-700">
                        <td class="py-1.5 text-sm text-gray-400">
                            {{ $employee['rank'] }}
                        </td>
                        <td class="py-1.5 text-sm">{{ $employee['name'] }}</td>
                        <td class="py-1.5 text-sm text-gray-500">
                            {{ $employee['department_name'] }}
                        </td>
                        <td
                            @class([
                                'py-1.5 text-right text-sm font-medium',
                                'text-red-600 dark:text-red-400' => $employee['overtime_raw'] > 20,
                                'text-amber-600 dark:text-amber-400' => $employee['overtime_raw'] > 10 && $employee['overtime_raw'] <= 20,
                            ])
                        >
                            {{ $employee['overtime_hours'] }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-4 text-center text-gray-400">
                            {{ __('No overtime data available') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
