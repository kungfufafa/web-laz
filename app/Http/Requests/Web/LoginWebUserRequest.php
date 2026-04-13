<?php

namespace App\Http\Requests\Web;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class LoginWebUserRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => PhoneNumber::normalize((string) $this->input('phone', '')),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^08[1-9][0-9]{6,11}$/'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Format nomor telepon harus dimulai dengan 08, 628, atau +628.',
        ];
    }
}
