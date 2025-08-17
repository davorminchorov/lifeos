<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExpenseControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302); // Redirects to login when not authenticated
    }
}
