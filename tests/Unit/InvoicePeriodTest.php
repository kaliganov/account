<?php

namespace Tests\Unit;

use App\Services\InvoicePeriod;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class InvoicePeriodTest extends TestCase
{
    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_september_stays_september_on_august_31(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-31 11:21:00', 'Europe/Moscow'));

        $parsed = InvoicePeriod::parse('2026-09');

        $this->assertSame(9, $parsed->month);
        $this->assertSame(2026, $parsed->year);
        $this->assertSame(1, $parsed->day);
        $this->assertSame('2026-09-30', InvoicePeriod::invoiceDate('2026-09')->toDateString());
        $this->assertSame('2026-08-31', InvoicePeriod::issueDate()->toDateString());
        $this->assertStringContainsString('сентябре 2026 г.', InvoicePeriod::servicesText('2026-09'));
        $this->assertStringNotContainsString('октябре', InvoicePeriod::servicesText('2026-09'));
    }

    public function test_april_stays_april_on_march_31(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-31 12:00:00', 'Europe/Moscow'));

        $this->assertSame(4, InvoicePeriod::parse('2026-04')->month);
        $this->assertSame('2026-04-30', InvoicePeriod::invoiceDate('2026-04')->toDateString());
        $this->assertStringContainsString('апреле', InvoicePeriod::servicesText('2026-04'));
    }

    public function test_options_include_plus_minus_twelve_months(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-31 11:21:00', 'Europe/Moscow'));

        $values = InvoicePeriod::allowedValues();

        $this->assertContains('2025-08', $values);
        $this->assertContains('2026-08', $values);
        $this->assertContains('2026-09', $values);
        $this->assertContains('2027-08', $values);
        $this->assertCount(25, $values);
    }
}
