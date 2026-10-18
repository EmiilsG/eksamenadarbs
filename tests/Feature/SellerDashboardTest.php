<?php

namespace Tests\Feature;

use App\Models\Favorite;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_auth(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_dashboard_displays_seller_statistics(): void
    {
        $seller = User::factory()->create(['name' => 'Anna Bērziņa']);
        $product = Product::factory()->for($seller)->create([
            'name' => 'Velosipēds',
            'price' => 150,
            'category' => 'Sports',
        ]);
        $product->views_count = 42;
        $product->save();

        Favorite::create(['user_id' => $seller->id, 'product_id' => $product->id]);

        $response = $this->actingAs($seller)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Pārdevēja statistika');
        $response->assertSee('Velosipēds');
        $response->assertSee('Sports');
        $response->assertSee(number_format(42));
    }

    public function test_dashboard_shows_zero_state_for_new_seller(): void
    {
        $seller = User::factory()->create();

        $response = $this->actingAs($seller)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Vēl nav produktu.');
        $response->assertSee('Vēl nav atsauksmju.');
        $response->assertSee('Jums vēl nav neviena sludinājuma');
    }

    public function test_dashboard_does_not_include_other_sellers_products(): void
    {
        $seller = User::factory()->create();
        Product::factory()->for($seller)->create(['name' => 'Mans produkts']);
        Product::factory()->create(['name' => 'Svešs produkts']);

        $this->actingAs($seller)->get('/dashboard')
            ->assertSee('Mans produkts')
            ->assertDontSee('Svešs produkts');
    }

    public function test_dashboard_shows_recent_reviews(): void
    {
        $seller = User::factory()->create();
        $reviewer = User::factory()->create(['name' => 'Jānis Kalns']);

        $this->actingAs($reviewer)->post('/profile/'.$seller->id.'/review', [
            'rating' => 5,
            'comment' => 'Lielisks pārdevējs, iesaku!',
        ]);

        $this->actingAs($seller)->get('/dashboard')
            ->assertOk()
            ->assertSee('Lielisks pārdevējs, iesaku!')
            ->assertSee('Jānis Kalns');
    }

    public function test_viewing_a_product_increments_view_count(): void
    {
        $product = Product::factory()->create();

        $this->get('/products/' . $product->id)->assertOk();

        $this->assertEquals(1, $product->fresh()->views_count);
    }

    public function test_owner_viewing_own_product_does_not_increment_view_count(): void
    {
        $owner = User::factory()->create();
        $product = Product::factory()->for($owner)->create();
        $product->views_count = 7;
        $product->save();

        $this->actingAs($owner)->get('/products/' . $product->id)->assertOk();

        $this->assertEquals(7, $product->fresh()->views_count);
    }

    public function test_product_page_shows_view_count(): void
    {
        $product = Product::factory()->create();
        $product->views_count = 123;
        $product->save();

        $this->get('/products/' . $product->id)
            ->assertOk()
            ->assertSee($product->fresh()->views_count . ' skatījumi');
    }

    public function test_dashboard_link_is_present_in_navigation(): void
    {
        $seller = User::factory()->create();

        $this->actingAs($seller)->get('/products')
            ->assertOk()
            ->assertSee(route('dashboard'));
    }

    public function test_dashboard_counts_reviews_and_rating(): void
    {
        $seller = User::factory()->create();
        $r1 = User::factory()->create();
        $r2 = User::factory()->create();

        $this->actingAs($r1)->post('/profile/'.$seller->id.'/review', ['rating' => 5, 'comment' => 'a']);
        $this->actingAs($r2)->post('/profile/'.$seller->id.'/review', ['rating' => 4, 'comment' => 'b']);

        $response = $this->actingAs($seller)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Atsauksmes pa zvaigznēm');
        $this->assertEquals(2, $seller->fresh()->reviews_count);
        $this->assertEquals(4.5, (float) $seller->fresh()->average_rating);
    }
}
