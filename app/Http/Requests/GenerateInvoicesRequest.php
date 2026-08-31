<?php

namespace App\Http\Requests;

use App\Services\InvoicePeriod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateInvoicesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'month' => ['required', 'date_format:Y-m', Rule::in(InvoicePeriod::allowedValues())],
            'check_number' => ['required', 'integer', 'min:1'],
            'counterparty_ids' => ['sometimes', 'array'],
            'counterparty_ids.*' => ['integer', 'distinct'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'month.required' => 'Выберите месяц.',
            'month.date_format' => 'Некорректный формат месяца.',
            'month.in' => 'Выбранный месяц недоступен.',
            'check_number.required' => 'Укажите стартовый номер счёта.',
            'check_number.integer' => 'Номер счёта должен быть целым числом.',
            'check_number.min' => 'Номер счёта должен быть не меньше 1.',
        ];
    }
}
