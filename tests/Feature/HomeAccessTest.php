<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomeAccessTest extends TestCase
{
    public function test_guest_is_redirected_from_home(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }
}
