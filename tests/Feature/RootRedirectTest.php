<?php

namespace Tests\Feature;

use Tests\TestCase;

class RootRedirectTest extends TestCase
{
    public function test_guests_are_redirected_from_root_to_the_login_page(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_the_login_page_is_reachable_for_guests(): void
    {
        $this->get('/login')->assertOk();
    }
}
