<?php

namespace Tests\Feature\Auth;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.login');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $customer = Customer::factory()->create();

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $customer->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component
            ->assertHasNoErrors()
            ->assertRedirect(route('home', absolute: false));

        $this->assertTrue(Auth::guard('customer')->check());
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $customer = Customer::factory()->create();

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $customer->email)
            ->set('form.password', 'wrong-password');

        $component->call('login');

        $component
            ->assertHasErrors()
            ->assertNoRedirect();

        $this->assertTrue(Auth::guard('customer')->guest());
    }

    public function test_navigation_menu_can_be_rendered(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs($customer, 'customer');

        $response = $this->get('/');

        $response->assertOk();
    }

    public function test_users_can_logout(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs($customer, 'customer');

        $component = Volt::test('layout.navigation');

        $component->call('logout');

        $component
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
    }
}
