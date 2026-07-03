<?php

namespace FluxErp\Traits\Livewire\Widget;

trait RespondsToApiRequests
{
    public function __invoke()
    {
        foreach (request()->validate($this->apiRules()) as $parameter => $value) {
            $this->{$parameter} = $value;
        }

        if (method_exists($this, 'mount')) {
            $this->mount();
        }

        return response()->json(['data' => $this->toApiResponse()]);
    }

    public function toApiResponse(): array
    {
        $data = [];

        foreach ($this->apiResponseProperties() as $property) {
            $data[$property] = $this->{$property};
        }

        return $data;
    }

    protected function apiRules(): array
    {
        return [];
    }

    protected function apiResponseProperties(): array
    {
        return [];
    }
}
