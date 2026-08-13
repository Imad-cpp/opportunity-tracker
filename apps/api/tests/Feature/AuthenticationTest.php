<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'sanctum.stateful' => ['localhost:3000'],
            'cors.allowed_origins' => ['http://localhost:3000'],
        ]);
    }

    public function test_registration_normalizes_identity_and_starts_session(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);
        $before = session()->getId();

        $response = $this->spa()->postJson('/api/v1/auth/register', [
            'name' => '  Test Student  ',
            'email' => '  Student@Example.Test ',
            'password' => 'Strong!Password123',
            'timezone' => 'Africa/Casablanca',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Test Student')
            ->assertJsonPath('data.email', 'student@example.test')
            ->assertJsonPath('data.timezone', 'Africa/Casablanca');

        $user = User::query()->firstOrFail();
        $this->assertTrue(Str::isUuid((string) $user->getKey()));
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($before, session()->getId());
    }

    public function test_invalid_timezone_uses_stable_validation_error(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);

        $this->spa()->postJson('/api/v1/auth/register', [
            'name' => 'Test Student',
            'email' => 'student@example.test',
            'password' => 'Strong!Password123',
            'timezone' => 'Mars/Olympus',
        ])->assertUnprocessable()
            ->assertJsonPath('error.code', 'VALIDATION_FAILED')
            ->assertJsonPath('error.details.timezone.0', 'The timezone field must be a valid IANA time zone.');
    }

    public function test_login_regenerates_session_and_logout_invalidates_it(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);
        $user = User::factory()->create([
            'email' => 'student@example.test',
            'password' => 'Strong!Password123',
        ]);
        $this->withSession(['marker' => 'before-login']);
        $before = session()->getId();

        $this->spa()->postJson('/api/v1/auth/login', [
            'email' => 'Student@Example.Test',
            'password' => 'Strong!Password123',
        ])->assertOk()
            ->assertJsonPath('data.id', (string) $user->getKey());

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($before, session()->getId());

        $this->spa()->postJson('/api/v1/auth/logout')->assertNoContent();
        $this->assertGuest();
    }

    public function test_failed_login_attempts_are_rate_limited_without_revealing_account_state(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);
        User::factory()->create(['email' => 'student@example.test']);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->spa()->postJson('/api/v1/auth/login', [
                'email' => 'student@example.test',
                'password' => 'Wrong!Password123',
            ])->assertUnprocessable()
                ->assertJsonPath('error.code', 'INVALID_CREDENTIALS');
        }

        $this->spa()->postJson('/api/v1/auth/login', [
            'email' => 'student@example.test',
            'password' => 'Wrong!Password123',
        ])->assertTooManyRequests()
            ->assertJsonPath('error.code', 'RATE_LIMITED');
    }

    public function test_private_account_endpoint_returns_stable_unauthenticated_error(): void
    {
        $this->spa()->getJson('/api/v1/me')
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');
    }

    public function test_sanctum_csrf_bootstrap_issues_xsrf_cookie(): void
    {
        $this->spa()->get('/sanctum/csrf-cookie')
            ->assertNoContent()
            ->assertCookie('XSRF-TOKEN');
    }

    public function test_untrusted_origin_is_not_reflected_by_cors(): void
    {
        $response = $this->withHeaders([
            'Origin' => 'https://evil.example',
            'Accept' => 'application/json',
        ])->getJson('/api/v1/health/live');

        $response->assertOk();
        $this->assertNotSame('https://evil.example', $response->headers->get('Access-Control-Allow-Origin'));
    }

    private function spa(): static
    {
        return $this->withHeaders([
            'Origin' => 'http://localhost:3000',
            'Referer' => 'http://localhost:3000/',
            'Accept' => 'application/json',
        ]);
    }
}
