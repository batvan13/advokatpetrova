<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'first_name' => trim((string) $this->input('first_name', '')),
            'last_name'  => trim((string) $this->input('last_name', '')),
            'email'      => trim((string) $this->input('email', '')),
            'body'       => trim((string) $this->input('body', '')),
        ]);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:50'],
            'last_name'  => ['required', 'string', 'max:50'],
            'email'      => ['required', 'email', 'max:255'],
            'body'       => ['required', 'string', 'min:20', 'max:3000'],
            'privacy'    => ['accepted'],
            'company'    => ['nullable', 'string', 'max:0'],
            'opened_at'  => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Моля, въведете вашето име.',
            'first_name.max'      => 'Името не може да надвишава 50 символа.',
            'last_name.required'  => 'Моля, въведете вашата фамилия.',
            'last_name.max'       => 'Фамилията не може да надвишава 50 символа.',
            'email.required'      => 'Моля, въведете имейл адрес.',
            'email.email'         => 'Имейл адресът не е валиден.',
            'email.max'           => 'Имейл адресът е прекалено дълъг.',
            'body.required'       => 'Моля, въведете отзив.',
            'body.min'            => 'Отзивът трябва да съдържа поне 20 символа.',
            'body.max'            => 'Отзивът не може да надвишава 3000 символа.',
            'privacy.accepted'    => 'Моля, приемете Политиката за поверителност.',
            'company.max'         => 'Моля, проверете данните и опитайте отново.',
            'opened_at.required'  => 'Моля, проверете данните и опитайте отново.',
            'opened_at.integer'   => 'Моля, проверете данните и опитайте отново.',
            'opened_at.min'       => 'Моля, проверете данните и опитайте отново.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            $opened = (int) $this->input('opened_at', 0);

            if ($opened > 0 && (time() - $opened) < 2) {
                $v->errors()->add('review', 'Моля, изчакайте момент и опитайте отново.');
            }
        });
    }
}
