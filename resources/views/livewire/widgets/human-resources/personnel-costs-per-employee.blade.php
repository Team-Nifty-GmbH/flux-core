<div class="flex h-full flex-col gap-4 p-4">
    <div>
        <h2
            class="truncate text-lg font-semibold text-gray-700 dark:text-gray-400"
        >
            {{ __('Personnel Costs Per Employee') }}
        </h2>
        <hr class="mt-2" />
    </div>
    <div class="overflow-auto">
        <table class="w-full">
            <thead>
                <tr
                    class="text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                >
                    <th class="pb-2">{{ __('Name') }}</th>
                    <th class="pb-2">{{ __('Department') }}</th>
                    <th class="pb-2 text-right">{{ __('Gross Salary') }}</th>
                    <th class="pb-2 text-right">
                        {{ __('Employer Contributions') }}
                    </th>
                    <th class="pb-2 text-right">{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($employees as $employee)
                    <tr class="border-t border-gray-100 dark:border-gray-700">
                        <td class="py-1.5 text-sm">{{ $employee['name'] }}</td>
                        <td class="py-1.5 text-sm text-gray-500">
                            {{ $employee['department_name'] }}
                        </td>
                        <td class="py-1.5 text-right text-sm">
                            {{ $employee['gross'] }}
                        </td>
                        <td class="py-1.5 text-right text-sm">
                            {{ $employee['employer_contributions'] }}
                        </td>
                        <td class="py-1.5 text-right text-sm font-medium">
                            {{ $employee['total'] }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-4 text-center text-gray-400">
                            {{ __('No data available') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
