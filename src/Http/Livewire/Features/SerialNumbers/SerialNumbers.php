<?php

namespace FluxErp\Http\Livewire\Features\SerialNumbers;

use FluxErp\Http\Requests\CreateSerialNumberRequest;
use FluxErp\Http\Requests\UpdateSerialNumberRequest;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Address;
use FluxErp\Models\Product;
use FluxErp\Models\SerialNumber;
use FluxErp\Services\SerialNumberService;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use WireUi\Traits\Actions;

class SerialNumbers extends Component
{
    use WithFileUploads, Actions;

    public string $modelType;

    public int $modelId;

    public bool $cardModal = false;

    public array $serialNumber = [];

    public array $serialNumbers = [];

    public array $additionalColumns = [];

    public array $attachmentsUpload = [];

    public $attachments = [];

    public mixed $existingAttachments;

    public string $slug = '';

    public array $slugs = [];

    public array $slugMap = [];

    public array $filterSlug = [];

    public bool $showSlugModal = false;

    /**
     * @var array
     */
    protected $rules = [];

    public function boot(): void
    {
        // get Additional columns
        $this->additionalColumns = AdditionalColumn::query()
            ->where('model_type', SerialNumber::class)
            ->get()
            ->toArray();

        $this->searchModel = Product::class;
    }

    public function mount(): void
    {
        $this->loadSerialNumbers();
    }

    public function getRules(): array
    {
        $additionalRules = array_fill_keys(Arr::pluck($this->additionalColumns, 'name'), 'string');

        $rules = ($this->serialNumber['id'] ?? false) ?
            (new UpdateSerialNumberRequest())->rules() :
            (new CreateSerialNumberRequest())->rules();

        return Arr::prependKeysWith(array_merge($rules, $additionalRules), 'serialNumber.');
    }

    /**
     * @throws ValidationException
     */
    public function updated($propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.features.serial-numbers.serial-numbers');
    }

    public function save(): void
    {
        $function = ($this->serialNumber['id'] ?? false) ? 'update' : 'create';
        $permission = $function === 'update' ? 'api.serial-numbers.put' : 'api.serial-numbers.post';

        if (! user_can($permission)) {
            $this->notification()->error(__('You dont have the permission to do that.'));

            return;
        }

        $this->resetErrorBag();
        $this->validate();

        $addressId = $this->modelType === Address::class
            ? $this->modelId
            : $this->modelType::query()
                ->whereKey($this->modelId)
                ->first()
                ->addresses()
                ->first()
                ->id;

        $this->serialNumber['address_id'] = $addressId;

        $service = new SerialNumberService();
        $response = $service->{$function}($this->serialNumber);

        if ($this->serialNumber['id'] ?? false) {
            $this->cardModal = false;
            $serialNumbers = Arr::keyBy($this->serialNumbers, 'id');
            $serialNumbers[$this->serialNumber['id']] = $response['data']->toArray();
            $this->serialNumbers = array_values($serialNumbers);
        } else {
            $this->serialNumber = $response->toArray();
            $this->serialNumbers[] = $this->serialNumber;
        }

        $this->notification()->success(__('Serial number saved'));
        $this->reset(['search']);
    }

    public function edit($id): void
    {
        $serialNumber = SerialNumber::query()
            ->whereKey($id)
            ->with('product')
            ->first();

        $this->attachments = [];
        $this->attachmentsUpload = [];
        $this->existingAttachments = $serialNumber->media->sortBy('custom_properties.slug');
        $this->slugs = array_filter(
            array_values(
                array_unique($this->existingAttachments->pluck('custom_properties.slug')->toArray())
            )
        );

        $this->cardModal = true;
        $this->serialNumber = $serialNumber->toArray();
    }

    public function create(): void
    {
        $this->attachments = [];
        $this->attachmentsUpload = [];
        $this->existingAttachments = [];
        $this->slug = '';
        $this->serialNumber = (new SerialNumber())->toArray();

        $this->cardModal = true;
    }

    public function delete(): void
    {
        if (! user_can('api.serial-numbers.{id}.delete')) {
            $this->notification()->error(__('You dont have the permission to do that.'));

            return;
        }

        (new SerialNumberService())->delete($this->serialNumber['id']);

        $serialNumbers = Arr::keyBy($this->serialNumbers, 'id');
        unset($serialNumbers[$this->serialNumber['id']]);

        $this->serialNumbers = array_values($serialNumbers);
        $this->cardModal = false;
        $this->notification()->success(__('Serial number deleted'));
    }

    public function updatedSerialNumberProductId($id): void
    {
        $this->serialNumber['product'] = Product::query()
            ->whereKey($id)
            ->first()
            ?->toArray();
    }

    private function loadSerialNumbers(): void
    {
        $record = $this->modelType::query()
            ->whereKey($this->modelId)
            ->first();

        $this->serialNumbers = $record->serialNumbers()
            ->with('product', 'media')
            ->get()
            ->toArray();
    }
}
