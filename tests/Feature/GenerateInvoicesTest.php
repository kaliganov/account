<?php

namespace Tests\Feature;

use App\Models\Counterparty;
use App\Models\Invoice;
use App\Models\User;
use App\Services\InvoicePdfGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

#[RequiresPhpExtension('pdo_sqlite')]
class GenerateInvoicesTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        File::deleteDirectory(storage_path('app/tmp/invoices'));
        parent::tearDown();
    }

    public function test_generate_on_august_31_keeps_september_period(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-31 11:21:00', 'Europe/Moscow'));

        $user = User::factory()->approved()->create(['check_number' => 10]);
        $counterparty = Counterparty::factory()->create([
            'user_id' => $user->id,
            'name' => 'ООО Ромашка',
        ]);

        $this->mock(InvoicePdfGenerator::class, function ($mock) use ($counterparty) {
            $mock->shouldReceive('generate')
                ->once()
                ->with(
                    Mockery::on(fn ($c) => (int) $c->id === (int) $counterparty->id),
                    '2026-09',
                    10,
                    1,
                )
                ->andReturn(['filename' => '10_romashka.pdf', 'content' => '%PDF-fake']);
        });

        $this->actingAs($user)
            ->post(route('home.generate'), [
                'month' => '2026-09',
                'check_number' => 10,
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('archive.month', '2026-09')
            ->assertSessionHas('archive.count', 1);

        $this->assertDatabaseHas('invoices', [
            'user_id' => $user->id,
            'counterparty_id' => $counterparty->id,
            'number' => 10,
            'period' => '2026-09',
            'issued_on' => '2026-08-31',
        ]);

        $this->assertSame(11, $user->fresh()->check_number);
        $this->assertSame(1, Invoice::query()->count());
    }

    public function test_generate_only_selected_counterparties(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-31 11:21:00', 'Europe/Moscow'));

        $user = User::factory()->approved()->create(['check_number' => 1]);
        $first = Counterparty::factory()->create(['user_id' => $user->id, 'name' => 'Первый']);
        $second = Counterparty::factory()->create(['user_id' => $user->id, 'name' => 'Второй']);

        $this->mock(InvoicePdfGenerator::class, function ($mock) use ($second) {
            $mock->shouldReceive('generate')
                ->once()
                ->with(
                    Mockery::on(fn ($c) => (int) $c->id === (int) $second->id),
                    '2026-09',
                    1,
                    1,
                )
                ->andReturn(['filename' => '1_second.pdf', 'content' => '%PDF-fake']);
        });

        $this->actingAs($user)
            ->post(route('home.generate'), [
                'month' => '2026-09',
                'check_number' => 1,
                'counterparty_ids' => [$second->id],
            ])
            ->assertRedirect(route('home'));

        $this->assertDatabaseHas('invoices', ['counterparty_id' => $second->id]);
        $this->assertDatabaseMissing('invoices', ['counterparty_id' => $first->id]);
    }
}
