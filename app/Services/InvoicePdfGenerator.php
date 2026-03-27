<?php

namespace App\Services;

use App\Models\Counterparty;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use setasign\Fpdi\Tcpdf\Fpdi;
use TCPDF_FONTS;

class InvoicePdfGenerator
{
    private ?string $fontRegular = null;
    private ?string $fontBold = null;

    /**
     * @return array{filename: string, content: string}
     */
    public function generate(Counterparty $counterparty, string $month, int $number, int $itemsCount = 1): array
    {
        $templatePath = config('invoice_pdf.template_path');
        if (! is_string($templatePath) || ! File::exists($templatePath)) {
            throw new \RuntimeException('Не найден шаблон PDF. Положи файл в storage/app/templates/pechat.pdf');
        }

        $today = CarbonImmutable::now()->locale('ru');
        $dateStr = $today->translatedFormat('d F Y').' г.';

        $services = $this->buildServicesText($month);

        $buyerLine1 = trim($counterparty->name.($counterparty->inn ? ', ИНН '.$counterparty->inn : ''));
        $buyerLine2Parts = [];
        if ($counterparty->contract_number) {
            $buyerLine2Parts[] = '№ '.$counterparty->contract_number;
        }
        if ($counterparty->contract_date) {
            $buyerLine2Parts[] = 'от '.Carbon::parse($counterparty->contract_date)->format('d.m.Y');
        }
        $buyerLine2 = implode(' ', $buyerLine2Parts);

        $sum = $counterparty->contract_price !== null
            ? number_format((float) $counterparty->contract_price, 2, ',', ' ')
            : null;

        $itemsSummary = $sum !== null
            ? "Всего наименований {$itemsCount}, на сумму {$sum} руб."
            : "Всего наименований {$itemsCount}";

        $sumWords = $counterparty->contract_price !== null
            ? $this->moneyToWordsRu((string) $counterparty->contract_price)
            : null;

        $coords = config('invoice_pdf.coords_mm', []);

        if (is_string($sum)) {
            // Подстройка координат сумм под ширину строки
            if (strlen($sum) === 9) {
                $coords['total']['x'] = 141;
                $coords['total2']['x'] = 167.5;
                $coords['total3']['x'] = 164.5;
                $coords['total4']['x'] = 164.5;
            } elseif (strlen($sum) === 10) {
                $coords['total']['x'] = 139.5;
                $coords['total2']['x'] = 166;
                $coords['total3']['x'] = 162.5;
                $coords['total4']['x'] = 162.5;
            }
        }

        $pdf = new Fpdi('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $this->registerFonts();

        $pageCount = $pdf->setSourceFile($templatePath);
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tplId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($tplId);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);

            if ($pageNo === 1) {
                $this->writeAt($pdf, $coords['invoice_title'] ?? null, "Счет на оплату № {$number} от {$dateStr}");
                $this->writeAt($pdf, $coords['buyer_line1'] ?? null, $buyerLine1);
                if ($buyerLine2 !== '') {
                    $this->writeAt($pdf, $coords['buyer_line2'] ?? null, $buyerLine2);
                }
                $this->writeAt($pdf, $coords['services'] ?? null, $services);

                if ($sum !== null) {
                    $this->writeAt($pdf, $coords['total'] ?? null, $sum);
                    $this->writeAt($pdf, $coords['total2'] ?? null, $sum);
                    $this->writeAt($pdf, $coords['total3'] ?? null, $sum);
                    $this->writeAt($pdf, $coords['total4'] ?? null, $sum);
                }

                $this->writeAt($pdf, $coords['items_summary'] ?? null, $itemsSummary);
                if ($sumWords !== null) {
                    $this->writeAt($pdf, $coords['sum_words'] ?? null, $sumWords);
                }
            }
        }

        $safeName = $this->sanitizeFilename($counterparty->name);
        $filename = "{$number}_{$safeName}.pdf";

        return [
            'filename' => $filename,
            'content' => $pdf->Output($filename, 'S'),
        ];
    }

    private function writeAt(Fpdi $pdf, ?array $cfg, string $text): void
    {
        $x = (float) ($cfg['x'] ?? 20);
        $y = (float) ($cfg['y'] ?? 20);
        $font = (float) ($cfg['font'] ?? 10);
        $bold = (bool) ($cfg['bold'] ?? false);

        $family = $this->fontRegular ?? config('invoice_pdf.font.fallback_family', 'dejavusans');
        if ($bold && $this->fontBold) {
            $family = $this->fontBold;
        }

        $pdf->SetFont((string) $family, '', $font);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($x, $y);
        $pdf->Write(0, $text);
    }

    private function registerFonts(): void
    {
        if ($this->fontRegular || $this->fontBold) {
            return;
        }

        $regular = config('invoice_pdf.font.regular_ttf');
        $bold = config('invoice_pdf.font.bold_ttf');

        if (is_string($regular) && File::exists($regular)) {
            $this->fontRegular = TCPDF_FONTS::addTTFfont($regular, 'TrueTypeUnicode', '', 96);
        }
        if (is_string($bold) && File::exists($bold)) {
            $this->fontBold = TCPDF_FONTS::addTTFfont($bold, 'TrueTypeUnicode', '', 96);
        }
    }

    private function buildServicesText(string $month): string
    {
        $dt = CarbonImmutable::createFromFormat('Y-m', $month);
        $monthNum = (int) $dt->format('n');
        $year = $dt->format('Y');

        $monthsPrep = [
            1 => 'январе',
            2 => 'феврале',
            3 => 'марте',
            4 => 'апреле',
            5 => 'мае',
            6 => 'июне',
            7 => 'июле',
            8 => 'августе',
            9 => 'сентябре',
            10 => 'октябре',
            11 => 'ноябре',
            12 => 'декабре',
        ];

        $m = $monthsPrep[$monthNum] ?? $dt->locale('ru')->translatedFormat('F');

        return "Бухгалтерское сопровождение в {$m} {$year}г";
    }

    private function moneyToWordsRu(string $amount): string
    {
        $normalized = str_replace([' ', ','], ['', '.'], trim($amount));
        if ($normalized === '' || ! preg_match('/^\d+(\.\d+)?$/', $normalized)) {
            $normalized = '0';
        }

        [$rubStr, $kopStr] = array_pad(explode('.', $normalized, 2), 2, '0');
        $rub = (int) ($rubStr === '' ? 0 : $rubStr);
        $kop = (int) str_pad(substr($kopStr, 0, 2), 2, '0');

        $rubWords = $this->numberToWordsRu($rub, true);
        $rubUnit = $this->morph($rub, 'рубль', 'рубля', 'рублей');

        $kop2 = str_pad((string) $kop, 2, '0', STR_PAD_LEFT);
        $kopUnit = $this->morph($kop, 'копейка', 'копейки', 'копеек');

        return $this->mbUcfirst(trim($rubWords.' '.$rubUnit.' '.$kop2.' '.$kopUnit));
    }

    private function numberToWordsRu(int $number, bool $useZero = false): string
    {
        if ($number === 0) {
            return $useZero ? 'ноль' : '';
        }

        $unitsMale = ['', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'];
        $unitsFemale = ['', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'];
        $teens = [
            10 => 'десять', 11 => 'одиннадцать', 12 => 'двенадцать', 13 => 'тринадцать', 14 => 'четырнадцать',
            15 => 'пятнадцать', 16 => 'шестнадцать', 17 => 'семнадцать', 18 => 'восемнадцать', 19 => 'девятнадцать',
        ];
        $tens = ['', 'десять', 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто'];
        $hundreds = ['', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот'];

        $groups = [
            [null, false],
            [['тысяча', 'тысячи', 'тысяч'], true],
            [['миллион', 'миллиона', 'миллионов'], false],
            [['миллиард', 'миллиарда', 'миллиардов'], false],
        ];

        $parts = [];
        $i = 0;
        while ($number > 0 && $i < count($groups)) {
            $triad = $number % 1000;
            $number = intdiv($number, 1000);

            if ($triad === 0) {
                $i++;
                continue;
            }

            $triadWords = [];
            $triadWords[] = $hundreds[intdiv($triad, 100)];

            $rest = $triad % 100;
            if ($rest >= 10 && $rest <= 19) {
                $triadWords[] = $teens[$rest];
            } else {
                $triadWords[] = $tens[intdiv($rest, 10)];
                $u = $rest % 10;
                $triadWords[] = ($groups[$i][1] ? $unitsFemale[$u] : $unitsMale[$u]);
            }

            $triadWords = array_values(array_filter($triadWords, fn ($w) => $w !== ''));

            $forms = $groups[$i][0];
            if ($forms !== null) {
                $triadWords[] = $this->morph($triad, $forms[0], $forms[1], $forms[2]);
            }

            array_unshift($parts, implode(' ', $triadWords));
            $i++;
        }

        return trim(implode(' ', $parts));
    }

    private function morph(int $n, string $form1, string $form2, string $form5): string
    {
        $n = abs($n) % 100;
        $n1 = $n % 10;
        if ($n > 10 && $n < 20) {
            return $form5;
        }
        if ($n1 > 1 && $n1 < 5) {
            return $form2;
        }
        if ($n1 === 1) {
            return $form1;
        }
        return $form5;
    }

    private function mbUcfirst(string $s): string
    {
        $s = trim($s);
        if ($s === '') {
            return $s;
        }
        $first = mb_substr($s, 0, 1, 'UTF-8');
        $rest = mb_substr($s, 1, null, 'UTF-8');
        return mb_strtoupper($first, 'UTF-8').$rest;
    }

    private function sanitizeFilename(string $name): string
    {
        $name = preg_replace('/[^\pL\pN\-_ ]/u', '', $name) ?? 'counterparty';
        $name = trim(preg_replace('/\s+/u', ' ', $name) ?? $name);
        $name = str_replace(' ', '_', $name);
        return $name !== '' ? $name : 'counterparty';
    }
}

