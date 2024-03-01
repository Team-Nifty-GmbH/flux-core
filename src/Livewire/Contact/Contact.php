<?php

namespace FluxErp\Livewire\Contact;

use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\Forms\ContactForm;
use FluxErp\Models\Contact as ContactModel;
use FluxErp\Traits\Livewire\WithFileUploads;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;
use WireUi\Traits\Actions;

class Contact extends Component
{
    use Actions, WithFileUploads, WithTabs;

    public ContactForm $contact;

    public $avatar;

    public bool $edit = false;

    #[Url]
    public string $tab = 'contact.addresses';

    public function mount(?int $id = null): void
    {
        $contact = app(ContactModel::class)->query()
            ->with(['mainAddress', 'categories:id'])
            ->whereKey($id)
            ->firstOrFail();
        $this->avatar = $contact->getAvatarUrl();

        $this->contact->fill($contact);
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

    #[Renderless]
    public function contactUpdated(): void
    {
        $this->contact->fill(app(ContactModel::class)->query()->whereKey($this->contact->id)->first());
    }

    #[Renderless]
    public function contactDeleted(): void
    {
        $this->redirectRoute('contacts', navigate: true);
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.contact.contact');
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('contact.addresses')
                ->label(__('Addresses'))
                ->isLivewireComponent()
                ->wireModel('contact'),
            TabButton::make('contact.orders')
                ->label(__('Orders'))
                ->isLivewireComponent()
                ->wireModel('contact'),
            TabButton::make('contact.communication')
                ->label(__('Communication'))
                ->isLivewireComponent()
                ->wireModel('contact.id'),
            TabButton::make('contact.projects')
                ->label(__('Projects'))
                ->isLivewireComponent()
                ->wireModel('contact.id'),
            TabButton::make('contact.tickets')
                ->label(__('Tickets'))
                ->isLivewireComponent()
                ->wireModel('contact.id'),
            TabButton::make('contact.work-times')
                ->label(__('Work Times'))
                ->isLivewireComponent()
                ->wireModel('contact.id'),
            TabButton::make('contact.accounting')
                ->label(__('Accounting'))
                ->isLivewireComponent()
                ->wireModel('contact'),
            TabButton::make('contact.statistics')
                ->label(__('Statistics')),
        ];
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

        $this->redirectRoute('contacts', navigate: true);
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

    #[Renderless]
    public function reloadContact(): void
    {
        $contact = app(ContactModel::class)->query()
            ->with(['mainAddress', 'categories:id'])
            ->whereKey($this->contact->id)
            ->firstOrFail();

        $this->contact->fill($contact);
    }

    public function updatedAvatar(): void
    {
        $this->collection = 'avatar';
        try {
            $this->saveFileUploadsToMediaLibrary(
                'avatar',
                $this->contact->id,
                app(ContactModel::class)->getMorphClass()
            );
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->avatar = app(ContactModel::class)->query()
            ->whereKey($this->contact->id)
            ->first()
            ->getAvatarUrl();
    }
}
