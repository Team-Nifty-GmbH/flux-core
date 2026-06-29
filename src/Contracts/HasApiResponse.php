<?php

namespace FluxErp\Contracts;

interface HasApiResponse
{
    public function toApiResponse(): array;
}
