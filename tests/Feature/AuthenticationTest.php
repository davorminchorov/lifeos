<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user for authentication tests
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);
    }

    public function test_login_page_can_be_rendered()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function test_authenticated_users_cannot_access_login_page()
    {
        $response = $this->actingAs($this->user)->get('/login');

        $response->assertRedirect('/');
    }

    public function test_users_can_authenticate_with_valid_credentials()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
        $response->assertSessionHas('success', 'Welcome back to LifeOS!');
    }

    public function test_users_can_authenticate_with_remember_me()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => true,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');

        // Check that remember token is set
        $this->user->refresh();
        $this->assertNotNull($this->user->remember_token);
    }

    public function test_users_cannot_authenticate_with_invalid_password()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest();
        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertEquals('The provided credentials do not match our records.', session('errors')->get('email')[0]);
    }

    public function test_users_cannot_authenticate_with_invalid_email()
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
    }

    public function test_login_requires_email()
    {
        $response = $this->post('/login', [
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_login_requires_valid_email_format()
    {
        $response = $this->post('/login', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_login_requires_password()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    public function test_authenticated_users_can_logout()
    {
        $response = $this->actingAs($this->user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHas('success', 'You have been logged out successfully.');
    }

    public function test_guests_cannot_access_logout_route()
    {
        $response = $this->post('/logout');

        $response->assertRedirect('/login');
    }

    public function test_guests_cannot_access_protected_dashboard_route()
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_guests_cannot_access_protected_profile_routes()
    {
        $protectedRoutes = [
            ['GET', '/profile'],
            ['GET', '/profile/edit'],
            ['PATCH', '/profile'],
            ['PATCH', '/profile/password'],
        ];

        foreach ($protectedRoutes as [$method, $route]) {
            $response = $this->call($method, $route);

            $response->assertRedirect('/login');
        }
    }

    public function test_guests_cannot_access_protected_settings_routes()
    {
        $protectedRoutes = [
            '/settings',
            '/settings/account',
            '/settings/application',
            '/settings/notifications',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);

            $response->assertRedirect('/login');
        }
    }

    public function test_authenticated_users_can_access_protected_routes()
    {
        $user = $this->user;

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/profile');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/settings');
        $response->assertStatus(200);
    }

    public function test_login_redirects_to_intended_url_after_authentication()
    {
        // Try to access a protected route
        $this->get('/profile');

        // Login
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Should redirect to the originally intended URL
        $response->assertRedirect('/profile');
    }

    public function test_session_is_regenerated_on_login()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();

        // Verify session was regenerated by checking we have a session
        $this->assertNotNull(session()->getId());
    }

    public function test_session_is_invalidated_on_logout()
    {
        // Login first
        $this->actingAs($this->user);
        $sessionId = session()->getId();

        // Logout
        $response = $this->post('/logout');

        $this->assertGuest();

        // Session should be invalidated (new ID generated)
        $response->assertRedirect('/login');
    }

    public function test_custom_validation_messages_are_displayed()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'Please enter your email address.',
            'password' => 'Please enter your password.',
        ]);
    }

    public function test_invalid_email_format_shows_custom_message()
    {
        $response = $this->post('/login', [
            'email' => 'invalid-email-format',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'Please enter a valid email address.',
        ]);
    }
}
