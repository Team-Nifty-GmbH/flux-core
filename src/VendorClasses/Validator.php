<?php

namespace FluxErp\VendorClasses;

use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Validator as BaseValidator;

class Validator extends BaseValidator
{
    public Model $model;

    public function addModel(Model $model)
    {
        $this->model = $model;
        $traits = class_uses_recursive($model);

        // extend validation rules depending on used traits
        if (in_array(HasAdditionalColumns::class, $traits)) {
            if ($rules = $model->hasAdditionalColumnsValidationRules()) {
                $this->addRules($rules);
            }
        }

        if (in_array(HasTranslations::class, $traits)) {
            if ($rules = $model->hasTranslationsValidationRules($this->getRules(), $this->getData())) {
                $this->addRules($rules);
            }
        }
    }
}
