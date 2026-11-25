<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /** Halaman awal mengarahkan ke login bila belum masuk. */
    public function test_the_application_redirects_guests_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('login', absolute: false));
    }
}
