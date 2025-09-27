<div
        class="space-y-6"
>
    <x-card>
        <div class="space-y-4">
            <x-textarea
                    :label="__('Comment')"
                    wire:model="absenceRequestForm.comment"
                    rows="3"
                    :hint="__('Add a comment for the status change (required for rejection and cancellation)')"
            />

            <div class="flex flex-wrap gap-2">
                @canAction(\FluxErp\Actions\AbsenceRequest\ApproveAbsenceRequest::class)
                <x-button
                        x-cloak
                        x-show="$wire.absenceRequestForm.state_enum !== '{{ \FluxErp\Enums\AbsenceRequestStateEnum::Approved->value }}'"
                        :text="__('Approve')"
                        color="emerald"
                        wire:click="approve()"
                        icon="check"
                />
                @endcanAction

                @canAction(\FluxErp\Actions\AbsenceRequest\RejectAbsenceRequest::class)
                <x-button
                        x-cloak
                        x-show="$wire.absenceRequestForm.state_enum !== '{{ \FluxErp\Enums\AbsenceRequestStateEnum::Rejected->value }}'"
                        :text="__('Reject')"
                        color="red"
                        wire:click="reject()"
                        icon="x-mark"
                        x-bind:disabled="! $wire.absenceRequestForm.comment"
                />
                @endcanAction


                @canAction(\FluxErp\Actions\AbsenceRequest\RevokeAbsenceRequest::class)
                <x-button
                        x-cloak
                        x-show="$wire.absenceRequestForm.state_enum !== '{{ \FluxErp\Enums\AbsenceRequestStateEnum::Revoked->value }}' && $wire.absenceRequestForm.state_enum !== '{{ \FluxErp\Enums\AbsenceRequestStateEnum::Pending->value }}'"
                        :text="__('Revoke')"
                        color="secondary"
                        wire:click="revoke()"
                        icon="no-symbol"
                        x-bind:disabled="! $wire.absenceRequestForm.comment"
                />
                @endcanAction
            </div>
        </div>
    </x-card>

    <x-card :header="__('Status History')">
        <div class="space-y-3">
            <template x-for="activity in $wire.activities" :key="activity.id">
                <div
                        class="flex items-start space-x-3 pb-3 border-b border-gray-200 dark:border-gray-700 last:border-0">
                    <div class="flex-shrink-0">
                        <template x-if="activity.event === 'approved'">
                            <x-icon name="check-circle" class="size-5 text-green-500"/>
                        </template>
                        <template x-if="activity.event === 'rejected'">
                            <x-icon name="x-circle" class="size-5 text-red-500"/>
                        </template>
                        <template x-if="activity.event === 'revoked'">
                            <x-icon name="no-symbol" class="size-5 text-gray-500"/>
                        </template>
                        <template x-if="!['approved', 'rejected', 'revoked'].includes(activity.event)">
                            <x-icon name="information-circle" class="size-5 text-blue-500"/>
                        </template>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ __('Status changed to') }} <span
                                        x-text="activity.event.charAt(0).toUpperCase() + activity.event.slice(1)"></span>
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400"
                               x-text="new Date(activity.created_at).toLocaleString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })"></p>
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1 flex gap-1 items-center">
                            <x-avatar
                                    borderless
                                    image
                                    xs
                                    x-bind:src="activity.causer?.avatar_url ?? '{{ route('icons', ['name' => 'user']) }}'"
                            />
                            <div x-text="activity.causer?.name">
                            </div>
                        </div>
                        <template x-if="activity.description">
                            <div class="mt-2 p-2 bg-gray-50 dark:bg-gray-800 rounded">
                                <p class="text-sm text-gray-700 dark:text-gray-300" x-text="activity.description"></p>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </x-card>
</div>
