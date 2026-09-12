<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class Recaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (empty($value)) {
            $fail('Por favor, confirmá que no sos un robot.');
            return;
        }

        $respuesta = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret_key'),
            'response' => $value,
        ]);

        if (!$respuesta->json('success')) {
            $fail('No pudimos verificar que no sos un robot. Intentá de nuevo.');
        }
    }
}