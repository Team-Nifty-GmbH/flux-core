<div
    x-on:click="{{ $onClick ?? "window.Livewire.dispatchTo('contacts::index', 'goToContactWithAddress', result.contact_id, result.id);" }}"
    class="dark:hover:bg-secondary-800 flex cursor-pointer space-x-2 px-10 py-2 hover:bg-blue-50">
    <div class="font-bold" x-text="result.company"></div>
    <div x-text="result.firstname"></div>
    <div x-text="result.lastname"></div>
</div>
