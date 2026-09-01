<?php

namespace FluxErp\Http\Requests;

use Closure;

class PasskeyBridgeStartRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'code_challenge' => ['required', 'string', 'size:43'],
            'redirect_uri' => ['required', 'string', 'max:255', $this->redirectUriRule()],
        ];
    }

    protected function redirectUriRule(): Closure
    {
        return function (string $attribute, string $value, Closure $fail): void {
            $scheme = parse_url($value, PHP_URL_SCHEME);
            $allowed = config(
                'flux.passkey_bridge.allowed_redirect_schemes',
                ['nuxbe']
            );

            if (! is_string($scheme) || ! in_array($scheme, $allowed, true)) {
                $fail('The :attribute scheme is not permitted.');
            }
        };
    }
}
