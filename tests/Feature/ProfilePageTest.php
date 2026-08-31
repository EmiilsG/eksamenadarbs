<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_renders_with_logged_in_user(): void
    {
        $this->seed(\Database\Seeders\ProductSeeder::class);

        $user = User::where('email', 'seller1@example.com')->first();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('Pārdevēja atsauksmes');
        $response->assertSee($user->name);
        $response->assertSee('Sludinājumi');
        $response->assertSee('Atsauksmes');
    }

    public function test_other_user_profile_page_renders(): void
    {
        $this->seed(\Database\Seeders\ProductSeeder::class);

        $viewing = User::where('email', 'seller1@example.com')->first();
        $target = User::where('email', 'seller3@example.com')->first();

        $response = $this->actingAs($viewing)->get('/profile/' . $target->id);

        $response->assertStatus(200);
        $response->assertSee($target->name);
        $response->assertSee('Pārdevēja atsauksmes');
    }

    public function test_profile_requires_auth(): void
    {
        $response = $this->get('/profile');
        $response->assertRedirect(route('login'));
    }
}
