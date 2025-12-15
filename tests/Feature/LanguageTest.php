<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // A. LANGUAGE SWITCH TESTS
    // ==========================================

    public function test_user_can_switch_to_bm(): void
    {
        $response = $this->get('/language/bm');

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'bm');
    }

    public function test_user_can_switch_to_en(): void
    {
        $response = $this->get('/language/en');

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'en');
    }

    public function test_authenticated_user_language_is_persisted(): void
    {
        $user = User::factory()->create(['language_preference' => 'en']);

        $response = $this->actingAs($user)->get('/language/bm');

        $response->assertRedirect();
        $this->assertEquals('bm', $user->fresh()->language_preference);
    }

    // ==========================================
    // B. INVALID LANGUAGE TESTS
    // ==========================================

    public function test_invalid_language_returns_error(): void
    {
        $response = $this->get('/language/fr');

        // Controller returns 400 for invalid language
        $response->assertStatus(400);
    }

    // ==========================================
    // C. LANGUAGE PERSISTENCE TESTS
    // ==========================================

    public function test_language_persists_in_session(): void
    {
        $this->get('/language/en');
        
        $response = $this->get('/');
        
        $response->assertSessionHas('locale', 'en');
    }

    public function test_guest_language_preference(): void
    {
        $this->get('/language/bm');
        
        $this->assertNull(auth()->user());
        $this->assertEquals('bm', session('locale'));
    }
}

