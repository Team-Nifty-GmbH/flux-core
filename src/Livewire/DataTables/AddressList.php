<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\Contact\CreateContact;
use FluxErp\Livewire\Forms\ContactForm;
use FluxErp\Models\Address;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class AddressList extends BaseDataTable
{
    protected string $view = 'flux::livewire.contact.contacts';

    protected string $model = Address::class;

    public array $enabledCols = [
        'avatar',
        'contact.customer_number',
        'is_main_address',
        'company',
        'firstname',
        'lastname',
        'street',
        'zip',
        'city',
    ];

    public array $formatters = [
        'avatar' => 'image',
    ];

    public ContactForm $contact;

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create'))
                ->color('primary')
                ->icon('plus')
                ->attributes([
                    'x-on:click' => '$wire.show()',
                ])
                ->when(fn () => resolve_static(CreateContact::class, 'canPerformAction', [false])),
        ];
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->with('contact.media');
    }

    protected function getReturnKeys(): array
    {
        return array_merge(parent::getReturnKeys(), ['contact_id']);
    }

    protected function itemToArray($item): array
    {
        $returnArray = parent::itemToArray($item);
        $returnArray['avatar'] = $item->getAvatarUrl();

        return $returnArray;
    }

    public function show(): void
    {
        $this->contact->reset();

        $this->js(
            <<<'JS'
               $openModal('new-contact');
            JS
        );
    }

    public function save(): false|RedirectResponse|Redirector
    {
        try {
            $this->contact->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->notification()->success(__('Contact saved'));

        return redirect(route('contacts.id?', ['id' => $this->contact->id]));
    }
}
