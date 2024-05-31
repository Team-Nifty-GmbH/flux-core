<?php

namespace FluxErp\Support\Validator;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Validation\Factory;

class ValidatorFactory extends Factory
{
    /**
     * Custom Validator Factory constructor
     */
    public function __construct(Translator $translator, Container $container)
    {
        parent::__construct($translator, $container);
        $this->setPresenceVerifier(app('validation.presence'));
    }

    /**
     * @return Validator|\Illuminate\Validation\Validator|mixed
     */
    protected function resolve(array $data, array $rules, array $messages, array $customAttributes)
    {
        if (is_null($this->resolver)) {
            return new Validator($this->translator, $data, $rules, $messages, $customAttributes);
        }

        return call_user_func($this->resolver, $this->translator, $data, $rules, $messages, $customAttributes);
    }
}
