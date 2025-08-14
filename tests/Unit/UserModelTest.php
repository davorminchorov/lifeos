<?php

namespace Tests\Unit;

use App\Models\Contract;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_fillable_attributes(): void
    {
        $fillable = ['name', 'email', 'password'];
        $user = new User();

        $this->assertEquals($fillable, $user->getFillable());
    }

    public function test_user_has_hidden_attributes(): void
    {
        $hidden = ['password', 'remember_token'];
        $user = new User();

        $this->assertEquals($hidden, $user->getHidden());
    }

    public function test_user_casts_attributes_correctly(): void
    {
        $user = new User();
        $casts = $user->getCasts();

        $this->assertArrayHasKey('email_verified_at', $casts);
        $this->assertArrayHasKey('password', $casts);
        $this->assertEquals('datetime', $casts['email_verified_at']);
        $this->assertEquals('hashed', $casts['password']);
    }

    public function test_password_is_hashed_when_set(): void
    {
        $user = User::factory()->create([
            'password' => 'plain-text-password'
        ]);

        $this->assertNotEquals('plain-text-password', $user->password);
        $this->assertTrue(Hash::check('plain-text-password', $user->password));
    }

    public function test_user_has_subscriptions_relationship(): void
    {
        $user = new User();
        $relationship = $user->subscriptions();

        $this->assertInstanceOf(HasMany::class, $relationship);
        $this->assertEquals('user_id', $relationship->getForeignKeyName());
    }

    public function test_user_can_have_multiple_subscriptions(): void
    {
        $user = User::factory()->create();
        $subscriptions = Subscription::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->subscriptions);
        $subscriptions->each(function ($subscription) use ($user) {
            $this->assertTrue($user->subscriptions->contains($subscription));
        });
    }

    public function test_user_factory_creates_valid_user(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->password);
        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
    }

    public function test_user_factory_can_create_with_custom_attributes(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];

        $user = User::factory()->create($userData);

        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
    }

    public function test_user_email_is_unique(): void
    {
        $email = 'test@example.com';
        User::factory()->create(['email' => $email]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['email' => $email]);
    }
}
