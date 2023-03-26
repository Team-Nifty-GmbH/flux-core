<?php

namespace FluxErp\Http\Requests;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Rules\ExistsWithIgnore;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Unique;
use Symfony\Component\HttpFoundation\Response;

class BaseFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ResponseHelper::createResponseFromBase(
                statusCode: Response::HTTP_UNPROCESSABLE_ENTITY,
                data: $validator->errors()->toArray()
            )
        );
    }

    public function getRules(array|Model $model): array
    {
        if (! method_exists($this, 'rules')) {
            return [];
        }

        if ($model instanceof Model) {
            $model = $model->toArray();
        }

        $rules = $this->rules();

        if (! ($model['id'] ?? false)) {
            return $rules;
        }

        $table = null;
        if (
            ($rules['id'] ?? false)
            && is_string($rules['id'])
            && $pos = strpos($rules['id'] ?? '', 'exists:')
        ) {
            $start = $pos + 7;
            $end = strpos($rules['id'], ',', $start);

            $table = substr($rules['id'], $pos + 7, $end - $start);
        }

        foreach ($rules as $key => $rule) {
            if (is_string($rule)) {
                $ruleArray = explode('|', $rule);
                $rules[$key] = $ruleArray;
            } else {
                $ruleArray = $rule;
            }

            foreach ($ruleArray as $ruleKey => $ruleItem) {
                if (
                    is_string($ruleItem) &&
                    str_starts_with($ruleItem, 'unique:') &&
                    substr_count($ruleItem, ',') < 2
                ) {
                    $rules[$key][$ruleKey] .= ',' . (
                        substr_count($ruleItem, ',') === 0 ?
                            $key . ',' . $model['id'] :
                            $model['id']
                    );
                } elseif ($ruleItem instanceof Unique) {
                    $rules[$key][$ruleKey] = $ruleItem->ignore($model['id']);
                } elseif ($ruleItem instanceof ExistsWithIgnore) {
                    if ($table) {
                        $rules[$key][$ruleKey] = $ruleItem->ignore(
                            DB::table($ruleItem->getTable())
                                ->where($ruleItem->getColumn(), $model['id'])
                                ->first($ruleItem->getColumn())
                                ?->{$ruleItem->getColumn()}
                        );
                    }
                }
            }
        }

        return $rules;
    }
}
