<?php

namespace Tests\Unit;

use App\Support\Inn;
use PHPUnit\Framework\TestCase;

class InnTest extends TestCase
{
    public function test_valid_legal_and_personal_inn(): void
    {
        $this->assertTrue(Inn::isValid('7707083893'));
        $this->assertTrue(Inn::isValid('500100732259'));
    }

    public function test_rejects_invalid_inn(): void
    {
        $this->assertFalse(Inn::isValid('1234567890'));
        $this->assertFalse(Inn::isValid('123'));
        $this->assertFalse(Inn::isValid('7707083894'));
    }
}
