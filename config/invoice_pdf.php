<?php

return [
    /*
     * Путь к PDF-шаблону счета.
     *
     * Важно: сам файл-шаблон не хранится в репозитории. Положи его сюда:
     * storage/app/templates/pechat.pdf
     */
    'template_path' => storage_path('app/templates/pechat.pdf'),

    /*
     * Шрифты для печати поверх PDF.
     *
     * TCPDF не содержит Arial “из коробки”, поэтому подключаем TTF.
     * Положи файлы сюда:
     * - storage/app/fonts/arial.ttf
     * - storage/app/fonts/arialbd.ttf (опционально для жирного)
     */
    'font' => [
        'regular_ttf' => storage_path('app/fonts/arial.ttf'),
        'bold_ttf' => storage_path('app/fonts/arialbd.ttf'),
        'fallback_family' => 'dejavusans',
    ],

    /*
     * Координаты (в миллиметрах) куда печатать значения поверх шаблона.
     */
    'coords_mm' => [
        // "Счет на оплату № {number} от {date}"
        'invoice_title' => ['x' => 12, 'y' => 50.5, 'font' => 14.3, 'bold' => true],

        // Блок "Покупатель"
        'buyer_line1' => ['x' => 37.3, 'y' => 73.5, 'font' => 10.3, 'bold' => true],
        'buyer_line2' => ['x' => 37.3, 'y' => 84.4, 'font' => 10.3, 'bold' => true],

        // Наименование услуги (строка в таблице)
        'services' => ['x' => 21, 'y' => 96.3, 'font' => 8],

        // "Итого/Всего к оплате" (сумма)
        'total' => ['x' => 142.5, 'y' => 96.3, 'font' => 8],
        'total2' => ['x' => 169, 'y' => 96.3, 'font' => 8],
        'total3' => ['x' => 166, 'y' => 102.3, 'font' => 10.3, 'bold' => true],
        'total4' => ['x' => 166, 'y' => 111.3, 'font' => 10.3, 'bold' => true],

        // "Всего наименований 1, на сумму X руб."
        'items_summary' => ['x' => 11.5, 'y' => 119, 'font' => 10],

        // Сумма прописью: "Девять тысяч рублей 00 копеек"
        'sum_words' => ['x' => 11.5, 'y' => 124, 'font' => 10, 'bold' => true],
    ],
];

