<div
x-cloak
x-show="printStore.editFirstPageHeader"
>
    <div class="pb-4 text-lg text-gray-600">Client</div>
    <div class="flex flex-col gap-4">
        <div class="flex justify-between items-center">
            <div>Name</div>
            <x-toggle
                x-on:change="firstPageHeaderStore.toggleElement($refs,'first-page-header-client-name')"
                x-bind:value="firstPageHeaderStore.visibleElements.map(e => e.id).includes('first-page-header-client-name')"
            />
        </div>
        <div class="flex justify-between items-center">
            <div>One-Line Address</div>
            <x-toggle
                x-on:change="firstPageHeaderStore.toggleElement($refs,'first-page-header-postal-address-one-line')"
                x-bind:value="firstPageHeaderStore.visibleElements.map(e => e.id).includes('first-page-header-postal-address-one-line')"
            />
        </div>
    </div>
    <div class="mb-4 mt-4 w-full border-t border-gray-300"></div>
    <div  class="flex items-center justify-between">
        <div class=" text-lg text-gray-600">Subject</div>
        <x-toggle
            x-on:change="firstPageHeaderStore.toggleElement($refs,'first-page-header-subject')"
            x-bind:value="firstPageHeaderStore.visibleElements.map(e => e.id).includes('first-page-header-subject')"
        />
    </div>
    <div class="mb-4 mt-4 w-full border-t border-gray-300"></div>
    <div class="pb-4 text-lg text-gray-600">Order Details</div>
    <div class="flex flex-col gap-4">
    <div class="flex justify-between items-center">
        <div>Address</div>
        <x-toggle
            x-on:change="firstPageHeaderStore.toggleElement($refs,'first-page-header-address')"
            x-bind:value="firstPageHeaderStore.visibleElements.map(e => e.id).includes('first-page-header-address')"
        />
    </div>
    <div class="flex justify-between items-center">
    <div>Order</div>
    <x-toggle
        x-on:change="firstPageHeaderStore.toggleElement($refs,'first-page-header-right-block')"
        x-bind:value="firstPageHeaderStore.visibleElements.map(e => e.id).includes('first-page-header-right-block')"
    />
    </div>
</div>



</div>
