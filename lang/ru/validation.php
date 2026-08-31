<?php

return [
    'accepted' => 'Поле :attribute должно быть принято.',
    'array' => 'Поле :attribute должно быть массивом.',
    'boolean' => 'Поле :attribute должно быть true или false.',
    'confirmed' => 'Подтверждение поля :attribute не совпадает.',
    'current_password' => 'Неверный текущий пароль.',
    'date' => 'Поле :attribute должно быть датой.',
    'date_format' => 'Поле :attribute не соответствует формату :format.',
    'decimal' => 'Поле :attribute должно содержать :decimal знаков после запятой.',
    'distinct' => 'Поле :attribute содержит повторяющееся значение.',
    'email' => 'Поле :attribute должно быть корректным email.',
    'in' => 'Выбранное значение для :attribute недопустимо.',
    'integer' => 'Поле :attribute должно быть целым числом.',
    'max' => [
        'numeric' => 'Поле :attribute не должно быть больше :max.',
        'string' => 'Поле :attribute не должно быть длиннее :max символов.',
        'array' => 'Поле :attribute не должно содержать более :max элементов.',
    ],
    'min' => [
        'numeric' => 'Поле :attribute должно быть не меньше :min.',
        'string' => 'Поле :attribute должно быть не короче :min символов.',
        'array' => 'Поле :attribute должно содержать не менее :min элементов.',
    ],
    'regex' => 'Поле :attribute имеет некорректный формат.',
    'required' => 'Поле :attribute обязательно.',
    'string' => 'Поле :attribute должно быть строкой.',
    'unique' => 'Такое значение поля :attribute уже используется.',

    'attributes' => [
        'name' => 'название',
        'email' => 'email',
        'password' => 'пароль',
        'inn' => 'ИНН',
        'contract_number' => 'номер договора',
        'contract_date' => 'дата договора',
        'contract_price' => 'сумма договора',
        'month' => 'месяц',
        'check_number' => 'номер счёта',
        'current_password' => 'текущий пароль',
    ],
];
