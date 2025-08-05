<?php

namespace FluxErp\Livewire\Contact;

use Exception;
use FluxErp\Contracts\OffersPrinting;
use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\Forms\ContactForm;
use FluxErp\Models\Contact as ContactModel;
use FluxErp\Models\Media;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\CreatesDocuments;
use FluxErp\Traits\Livewire\WithFileUploads;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;
use Spatie\MediaLibrary\Support\MediaStream;
use Spatie\Permission\Exceptions\UnauthorizedException;

class Contact extends Component
{
    use Actions, CreatesDocuments, WithFileUploads, WithTabs;

    public $avatar;

    public ContactForm $contact;

    public bool $edit = false;

    #[Url]
    public string $tab = 'contact.addresses';

    public function mount(?int $id = null): void
    {
        $contact = resolve_static(ContactModel::class, 'query')
            ->with(['mainAddress', 'categories:id'])
            ->whereKey($id)
            ->firstOrFail();
        $this->avatar = $contact->getAvatarUrl();

        $this->contact->fill($contact);
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.contact.contact');
    }

    #[Renderless]
    public function contactDeleted(): void
    {
        $this->redirectRoute('contacts.contacts', navigate: true);
    }

    #[Renderless]
    public function contactUpdated(): void
    {
        $this->contact->fill(resolve_static(ContactModel::class, 'query')->whereKey($this->contact->id)->first());
    }

    public function createDocuments(): null|MediaStream|Media
    {
        return $this->createDocumentFromItems(
            resolve_static(ContactModel::class, 'query')->whereKey($this->contact->id)->firstOrFail(),
        );
    }

    #[Renderless]
    public function delete(): void
    {
        try {
            $this->contact->delete();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->redirectRoute('contacts.contacts', navigate: true);
    }

    #[Renderless]
    public function getListeners(): array
    {
        $model = app(ContactModel::class);
        $model->id = $this->contact->id;
        $channel = $model->broadcastChannel(false);

        return [
            'echo-private:' . $channel . ',.ContactUpdated' => 'contactUpdated',
            'echo-private:' . $channel . ',.ContactDeleted' => 'contactDeleted',
        ];
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('contact.addresses')
                ->text(__('Addresses'))
                ->isLivewireComponent()
                ->wireModel('contact'),
            TabButton::make('contact.orders')
                ->text(__('Orders'))
                ->isLivewireComponent()
                ->wireModel('contact'),
            TabButton::make('contact.leads')
                ->text(__('Leads'))
                ->isLivewireComponent()
                ->wireModel('contact.id'),
            TabButton::make('contact.communication')
                ->text(__('Communication'))
                ->isLivewireComponent()
                ->wireModel('contact.id'),
            TabButton::make('contact.projects')
                ->text(__('Projects'))
                ->isLivewireComponent()
                ->wireModel('contact.id'),
            TabButton::make('contact.attachments')
                ->text(__('Attachments'))
                ->isLivewireComponent()
                ->wireModel('contact.id'),
            TabButton::make('contact.tickets')
                ->text(__('Tickets'))
                ->isLivewireComponent()
                ->wireModel('contact.id'),
            TabButton::make('contact.work-times')
                ->text(__('Work Times'))
                ->isLivewireComponent()
                ->wireModel('contact.id'),
            TabButton::make('contact.accounting')
                ->text(__('Accounting'))
                ->isLivewireComponent()
                ->wireModel('contact'),
            TabButton::make('contact.statistics')
                ->text(__('Statistics')),
        ];
    }

    #[Renderless]
    public function reloadContact(): void
    {
        $contact = resolve_static(ContactModel::class, 'query')
            ->with(['mainAddress', 'categories:id'])
            ->whereKey($this->contact->id)
            ->firstOrFail();

        $this->contact->fill($contact);
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->contact->save();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->edit = false;

        return true;
    }

    public function updatedAvatar(): void
    {
        $this->collection = 'avatar';
        try {
            $this->saveFileUploadsToMediaLibrary(
                'avatar',
                $this->contact->id,
                morph_alias(ContactModel::class)
            );
        } catch (Exception $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->avatar = resolve_static(ContactModel::class, 'query')
            ->whereKey($this->contact->id)
            ->first()
            ->getAvatarUrl();
    }

    protected function getPrintLayouts(): array
    {
        return app(ContactModel::class)->resolvePrintViews();
    }

    protected function getSubject(OffersPrinting $item): string
    {
        return __('Balance Statement :date', ['date' => now()->format('d.m.Y')]);
    }

    protected function getTo(OffersPrinting $item, array $documents): array
    {
        return [$item->invoiceAddress->email_primary ?? $item->mainAddress->email_primary];
    }
}
