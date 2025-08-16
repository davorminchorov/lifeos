<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        $this->otherUser = User::factory()->create();
    }

    public function test_profile_show_displays_user_profile()
    {
        $response = $this->actingAs($this->user)->get('/profile');

        $response->assertStatus(200);
        $response->assertViewIs('profile.show');
        $response->assertViewHas('user', $this->user);
        $response->assertSee('John Doe');
        $response->assertSee('john@example.com');
    }

    public function test_profile_show_requires_authentication()
    {
        $response = $this->get('/profile');

        $response->assertRedirect('/login');
    }

    public function test_profile_edit_displays_edit_form()
    {
        $response = $this->actingAs($this->user)->get('/profile/edit');

        $response->assertStatus(200);
        $response->assertViewIs('profile.edit');
        $response->assertViewHas('user', $this->user);
        $response->assertSee('John Doe');
        $response->assertSee('john@example.com');
    }

    public function test_profile_edit_requires_authentication()
    {
        $response = $this->get('/profile/edit');

        $response->assertRedirect('/login');
    }

    public function test_profile_update_modifies_user_data()
    {
        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHas('status', 'Profile updated successfully!');

        $this->user->refresh();
        $this->assertEquals('Jane Smith', $this->user->name);
        $this->assertEquals('jane@example.com', $this->user->email);
    }

    public function test_profile_update_requires_authentication()
    {
        $response = $this->patch('/profile', [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_profile_update_validates_required_fields()
    {
        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => '',
            'email' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'email']);

        $this->user->refresh();
        $this->assertEquals('John Doe', $this->user->name);
        $this->assertEquals('john@example.com', $this->user->email);
    }

    public function test_profile_update_validates_email_format()
    {
        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => 'Jane Smith',
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors(['email']);

        $this->user->refresh();
        $this->assertEquals('john@example.com', $this->user->email);
    }

    public function test_profile_update_validates_unique_email()
    {
        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => 'Jane Smith',
            'email' => $this->otherUser->email,
        ]);

        $response->assertSessionHasErrors(['email']);

        $this->user->refresh();
        $this->assertEquals('john@example.com', $this->user->email);
    }

    public function test_profile_update_allows_same_email()
    {
        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => 'Jane Smith',
            'email' => $this->user->email,
        ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHas('status', 'Profile updated successfully!');

        $this->user->refresh();
        $this->assertEquals('Jane Smith', $this->user->name);
        $this->assertEquals('john@example.com', $this->user->email);
    }

    public function test_password_update_changes_password()
    {
        $newPassword = 'new-secure-password';

        $response = $this->actingAs($this->user)->patch('/profile/password', [
            'current_password' => 'password', // Default factory password
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHas('status', 'Password updated successfully!');

        $this->user->refresh();
        $this->assertTrue(Hash::check($newPassword, $this->user->password));
    }

    public function test_password_update_requires_authentication()
    {
        $response = $this->patch('/profile/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_password_update_validates_current_password()
    {
        $response = $this->actingAs($this->user)->patch('/profile/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ]);

        $response->assertSessionHasErrors(['current_password']);
    }

    public function test_password_update_validates_required_fields()
    {
        $response = $this->actingAs($this->user)->patch('/profile/password', [
            'current_password' => '',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors(['current_password', 'password']);
    }

    public function test_password_update_validates_password_confirmation()
    {
        $response = $this->actingAs($this->user)->patch('/profile/password', [
            'current_password' => 'password',
            'password' => 'new-secure-password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_password_update_validates_password_strength()
    {
        $response = $this->actingAs($this->user)->patch('/profile/password', [
            'current_password' => 'password',
            'password' => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_profile_routes_are_protected_by_auth_middleware()
    {
        // Test all profile routes require authentication
        $routes = [
            ['GET', '/profile'],
            ['GET', '/profile/edit'],
            ['PATCH', '/profile'],
            ['PATCH', '/profile/password'],
        ];

        foreach ($routes as [$method, $route]) {
            $response = $this->call($method, $route);
            $response->assertRedirect('/login');
        }
    }
}
