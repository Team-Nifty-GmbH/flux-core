<x-modal id="upload-document-modal" :title="__('Upload document')">
    <div class="flex flex-col gap-1.5">
        <x-select.styled
            :label="__('Document Type')"
            wire:model="documentForm.document_type_id"
            :options="$documentTypes"
            required
        />
        <x-date
            :label="__('Document date')"
            wire:model="documentForm.document_date"
            required
        />
        <x-input
            :label="__('Name')"
            :hint="__('Leave empty to use the file name.')"
            wire:model="documentForm.name"
        />
        <x-upload
            :label="__('File')"
            wire:model="documentForm.media"
            accept="application/pdf,image/jpeg,image/png"
        />
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$tsui.close.modal('upload-document-modal')"
        />
        <x-button
            color="indigo"
            :text="__('Upload')"
            loading="saveUpload"
            x-on:click="
                $wire.saveUpload().then((success) => {
                    if (success) $tsui.close.modal('upload-document-modal');
                })
            "
        />
    </x-slot:footer>
</x-modal>

<x-modal id="review-document-modal" :title="__('Review document')">
    <x-textarea
        :label="__('Note')"
        :hint="__('Visible to the employee, e.g. why the document was rejected.')"
        wire:model="reviewNote"
    />
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$tsui.close.modal('review-document-modal')"
        />
        <x-button
            color="red"
            :text="__('Reject')"
            x-on:click="
                $wire.saveReview('rejected').then((success) => {
                    if (success) $tsui.close.modal('review-document-modal');
                })
            "
        />
        <x-button
            color="emerald"
            :text="__('Approve')"
            x-on:click="
                $wire.saveReview('approved').then((success) => {
                    if (success) $tsui.close.modal('review-document-modal');
                })
            "
        />
    </x-slot:footer>
</x-modal>

<x-modal id="extend-retention-modal" :title="__('Extend retention')">
    <x-date :label="__('Retain until')" wire:model="retainUntil" required />
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$tsui.close.modal('extend-retention-modal')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            x-on:click="
                $wire.saveRetention().then((success) => {
                    if (success) $tsui.close.modal('extend-retention-modal');
                })
            "
        />
    </x-slot:footer>
</x-modal>
