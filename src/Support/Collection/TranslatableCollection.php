<?php

namespace FluxErp\Support\Collection;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Session;

class TranslatableCollection extends Collection
{
    public function localize(?int $languageId = null)
    {
        $languageId ??= Session::get('selectedLanguageId');

        if (is_null($languageId)) {
            return $this;
        }

        return $this->transform(fn ($model) => $model->localize($languageId));
    }
}
