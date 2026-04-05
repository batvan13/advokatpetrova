<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class InquiryRequest extends FormRequest
{
    private const NAME_MAX = 100;

    private const MESSAGE_MAX = 2000;

    private const SUBJECT_PREFIX_LABEL = '[Относно]: ';

    private const SUBJECT_MIN_PRESERVE = 30;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $first = trim((string) $this->input('first_name', ''));
        $last = trim((string) $this->input('last_name', ''));
        if ($first !== '' || $last !== '') {
            $merged = trim($first.' '.$last);
            $this->merge(['name' => trim(mb_substr($merged, 0, self::NAME_MAX))]);
        }

        $subject = trim((string) $this->input('subject', ''));
        $body = trim((string) $this->input('message_body', ''));
        if ($subject !== '' || $body !== '') {
            $this->merge(['message' => self::boundedMergedMessage($subject, $body)]);
        }
    }

    /**
     * Merge as "[Относно]: {subject…}\n\n{body}" under MESSAGE_MAX; always preserve at least
     * min(30, subject length) characters of subject when subject is non-empty.
     */
    private static function boundedMergedMessage(string $subject, string $body): string
    {
        $max = self::MESSAGE_MAX;
        $sep = "\n\n";
        $label = self::SUBJECT_PREFIX_LABEL;
        $labelLen = mb_strlen($label);
        $sepLen = mb_strlen($sep);

        if ($subject === '' && $body === '') {
            return '';
        }
        if ($subject === '') {
            return mb_substr($body, 0, $max);
        }
        if ($body === '') {
            $head = $label.mb_substr($subject, 0, max(0, $max - $labelLen));

            return mb_substr($head, 0, $max);
        }

        $subLen = mb_strlen($subject);
        $bodyLen = mb_strlen($body);
        $minSubKeep = min(self::SUBJECT_MIN_PRESERVE, $subLen);

        $full = $label.$subject.$sep.$body;
        if (mb_strlen($full) <= $max) {
            return $full;
        }

        $subBudget = $max - $labelLen - $sepLen - $bodyLen;
        if ($subBudget >= $subLen) {
            return $full;
        }

        if ($subBudget >= $minSubKeep) {
            return $label.mb_substr($subject, 0, $subBudget).$sep.$body;
        }

        $subPart = mb_substr($subject, 0, $minSubKeep);
        $prefix = $label.$subPart.$sep;
        $bodyRoom = $max - mb_strlen($prefix);

        return $prefix.mb_substr($body, 0, max(0, $bodyRoom));
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:30'],
            'message'    => ['required', 'string', 'min:10', 'max:2000'],
            'first_name' => ['nullable', 'string', 'max:50'],
            'last_name'  => ['nullable', 'string', 'max:50'],
            'subject'    => ['nullable', 'string', 'max:255'],
            'message_body' => ['nullable', 'string', 'max:2000'],
            'company'    => ['nullable', 'string', 'max:0'],
            'opened_at'  => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'       => 'Моля, въведете вашето име.',
            'name.max'            => 'Името не може да надвишава 100 символа.',
            'email.required'      => 'Моля, въведете имейл адрес.',
            'email.email'         => 'Имейл адресът не е валиден.',
            'email.max'           => 'Имейл адресът е прекалено дълъг.',
            'phone.max'           => 'Телефонният номер не може да надвишава 30 символа.',
            'message.required'    => 'Моля, въведете съобщение.',
            'message.min'         => 'Съобщението трябва да съдържа поне 10 символа.',
            'message.max'         => 'Съобщението не може да надвишава 2000 символа.',
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
                $v->errors()->add('inquiry', 'Моля, изчакайте момент и опитайте отново.');
            }
        });
    }
}
