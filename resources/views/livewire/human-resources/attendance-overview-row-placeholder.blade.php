<tr
    x-cloak
    x-show="isDepartmentExpanded('{{ $departmentId }}')"
    class="animate-pulse"
>
    <td class="border-b border-r px-4 py-2 dark:border-gray-700">
        <div class="h-4 w-32 rounded bg-gray-200 dark:bg-gray-700"></div>
    </td>
    <td class="border-b border-r bg-blue-50/50 px-2 py-2 dark:border-gray-700 dark:bg-blue-900/20">
        <div class="mx-auto h-4 w-8 rounded bg-gray-200 dark:bg-gray-700"></div>
    </td>
    <td class="border-b border-r bg-purple-50/50 px-2 py-2 dark:border-gray-700 dark:bg-purple-900/20">
        <div class="mx-auto h-4 w-12 rounded bg-gray-200 dark:bg-gray-700"></div>
    </td>
    @foreach ($calendarDays as $calDay)
        <td class="{{ data_get($calDay, 'isWeekend') ? 'bg-gray-100 dark:bg-gray-700' : '' }} border-b border-r px-1 py-1 dark:border-gray-700">
            <div class="mx-auto size-6 rounded bg-gray-200 dark:bg-gray-600"></div>
        </td>
    @endforeach
</tr>
