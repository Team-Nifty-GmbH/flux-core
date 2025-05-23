<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\Contact\CreateContact;
use FluxErp\Actions\Lead\CreateLead;
use FluxErp\Contracts\OffersPrinting;
use FluxErp\Livewire\Forms\ContactForm;
use FluxErp\Livewire\Forms\LeadForm;
use FluxErp\Models\Address;
use FluxErp\Models\Media;
use FluxErp\Traits\Livewire\CreatesDocuments;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\MediaLibrary\Support\MediaStream;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class AddressList extends BaseDataTable
{
    use CreatesDocuments;

    public ContactForm $contact;

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

    public bool $isSelectable = true;

    public LeadForm $leadForm;

    public bool $showMap = false;

    protected ?string $includeBefore = 'flux::livewire.contact.contacts';

    protected string $model = Address::class;

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Show on Map'))
                ->color('indigo')
                ->icon('globe-alt')
                ->wireClick('$toggle(\'showMap\', true)'),
            DataTableButton::make()
                ->text(__('Create'))
                ->color('indigo')
                ->icon('plus')
                ->attributes([
                    'x-on:click' => '$wire.show()',
                ])
                ->when(fn () => resolve_static(CreateContact::class, 'canPerformAction', [false])),
        ];
    }

    protected function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->icon('document-text')
                ->text(__('Create Documents'))
                ->color('indigo')
                ->wireClick('openCreateDocumentsModal'),
            DataTableButton::make()
                ->text(__('Send Mail'))
                ->color('indigo')
                ->wireClick('createMailMessage'),
            DataTableButton::make()
                ->text(__('Create leads'))
                ->color('indigo')
                ->wireClick('openLeadsModal')
                ->when(fn () => resolve_static(CreateLead::class, 'canPerformAction', [false])),
        ];
    }

    #[Renderless]
    public function createDocuments(): null|MediaStream|Media
    {
        $response = $this->createDocumentFromItems($this->getSelectedModels(), true);
        $this->loadData();
        $this->reset('selected');

        return $response;
    }

    #[Renderless]
    public function createLeads(): bool
    {
        $created = 0;
        foreach ($this->getSelectedModelsQuery()->with('contact:id,agent_id')->get(['id', 'contact_id']) as $address) {
            $leadForm = clone $this->leadForm;
            $leadForm->address_id = $address->getKey();
            $leadForm->user_id = $address->contact?->agent_id ?? auth()->id();

            try {
                $leadForm->save();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);

                return false;
            }

            $created++;
        }

        $this->toast()->success(__(':count leads created', ['count' => $created]))->send();

        return true;
    }

    #[Renderless]
    public function createMailMessage(): void
    {
        $mailMessages = [];
        foreach ($this->getSelectedModels() as $model) {
            $mailMessages[] = [
                'to' => $this->getTo($model, []),
                'cc' => $this->getCc($model),
                'bcc' => $this->getBcc($model),
                'subject' => null,
                'html_body' => null,
                'communicatable_type' => $this->getCommunicatableType($model),
                'communicatable_id' => $this->getCommunicatableId($model),
            ];
        }

        $sessionKey = 'mail_' . Str::uuid()->toString();
        session()->put($sessionKey, $mailMessages);

        $this->dispatch('createFromSession', key: $sessionKey)->to('edit-mail');
    }

    #[Renderless]
    public function evaluate(): void {}

    #[Renderless]
    public function loadData(): void
    {
        parent::loadData();

        if ($this->showMap) {
            $this->updatedShowMap();
        }
    }

    #[Renderless]
    public function loadMap(): array
    {
        return $this->buildSearch()
            ->limit(100)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('is_main_address', true)
            ->select([
                'id',
                'contact_id',
                'latitude',
                'longitude',
                'company',
                'firstname',
                'lastname',
                'street',
                'zip',
                'city',
            ])
            ->with([
                'contact:id',
                'contact.media' => fn ($query) => $query->where('collection_name', 'avatar'),
            ])
            ->get()
            ->toMap()
            ->toArray();
    }

    #[Renderless]
    public function openLeadsModal(): void
    {
        $this->leadForm->reset();
        $this->leadForm->openModal();
    }

    #[Renderless]
    public function save(): false|RedirectResponse|Redirector
    {
        try {
            $this->contact->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->notification()->success(__(':model saved', ['model' => __('Contact')]))->send();

        return redirect(route('contacts.id?', ['id' => $this->contact->id]));
    }

    #[Renderless]
    public function show(): void
    {
        $this->contact->reset();

        $this->js(
            <<<'JS'
               $modalOpen('new-contact-modal');
            JS
        );
    }

    #[Renderless]
    public function updatedShowMap(): void
    {
        $this->dispatch('load-map');
    }

    protected function getBuilder(Builder $builder): Builder
    {
        // add contact_id to the select statement to ensure that the contact route is available
        return $builder->addSelect('contact_id')->with('contact.media');
    }

    protected function getHtmlBody(OffersPrinting $item): string
    {
        return '';
    }

    protected function getPrintLayouts(): array
    {
        return app(Address::class)->getPrintViews();
    }

    protected function getReturnKeys(): array
    {
        return array_merge(parent::getReturnKeys(), ['contact_id']);
    }

    protected function getSubject(OffersPrinting $item): string
    {
        return __('Address Label');
    }

    protected function getTo(OffersPrinting $item, array $documents): array
    {
        return Arr::wrap(
            $item->email_primary ?? $item->contactOptions->where('type', 'email')->first()?->value ?? []
        );
    }

    protected function itemToArray($item): array
    {
        $returnArray = parent::itemToArray($item);
        $returnArray['avatar'] = $item->getAvatarUrl();

        return $returnArray;
    }
}
