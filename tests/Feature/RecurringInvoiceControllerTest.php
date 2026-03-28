<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecurringInvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verify authenticated users can access the home page.
     */
    public function test_authenticated_user_can_access_home(): void
    {
        $this->setupTenantContext();

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
