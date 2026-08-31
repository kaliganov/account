<?php

namespace Database\Factories;

use App\Models\Counterparty;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Counterparty>
 */
class CounterpartyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => 'ООО '.$this->faker->unique()->company(),
            'inn' => '7707083893',
            'contract_number' => 'Д-'.$this->faker->numerify('####'),
            'contract_date' => '2026-01-15',
            'contract_price' => 10000.00,
        ];
    }
}
