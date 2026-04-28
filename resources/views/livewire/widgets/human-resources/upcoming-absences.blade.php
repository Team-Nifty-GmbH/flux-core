<div class="flex h-full flex-col gap-4 p-4">
    <div>
        <h2
            class="truncate text-lg font-semibold text-gray-700 dark:text-gray-400"
        >
            {{ __('Upcoming Absences') }}
        </h2>
        <hr class="mt-2" />
    </div>
    <div class="overflow-auto">
        <table class="w-full">
            <thead>
                <tr
                    class="text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                >
                    <th class="pb-2">{{ __('Employee') }}</th>
                    <th class="pb-2">{{ __('Type') }}</th>
                    <th class="pb-2 text-right">{{ __('From') }}</th>
                    <th class="pb-2 text-right">{{ __('To') }}</th>
                    <th class="pb-2 text-right">{{ __('Days') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($absences as $absence)
                    <tr class="border-t border-gray-100 dark:border-gray-700">
                        <td class="py-1.5 text-sm">
                            {{ $absence['employee_name'] }}
                        </td>
                        <td class="py-1.5">
                            <span
                                class="inline-flex items-center gap-1.5 text-sm"
                            >
                                <span
                                    class="h-2.5 w-2.5 rounded-full"
                                    style="background-color: {{ $absence['color'] }}"
                                ></span>
                                {{ $absence['absence_type'] }}
                            </span>
                        </td>
                        <td class="py-1.5 text-right text-sm">
                            {{ $absence['start_date'] }}
                        </td>
                        <td class="py-1.5 text-right text-sm">
                            {{ $absence['end_date'] }}
                        </td>
                        <td class="py-1.5 text-right text-sm font-medium">
                            {{ $absence['days'] }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-4 text-center text-gray-400">
                            {{ __('No upcoming absences') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
