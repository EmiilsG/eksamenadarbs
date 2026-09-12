<?php

namespace Tests\Feature;

use App\Models\Favorite;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_viewing_favorites(): void
    {
        $this->get('/favorites')->assertRedirect(route('login'));
    }

    public function test_guest_cannot_add_a_favorite(): void
    {
        $seller = User::factory()->create();
        $product = Product::factory()->for($seller)->create();

        $this->post('/products/' . $product->id . '/favorite')->assertRedirect(route('login'));
        $this->assertDatabaseCount('favorites', 0);
    }

    public function test_user_can_add_a_product_to_favorites(): void
    {
        $user = User::factory()->create();
        $seller = User::factory()->create();
        $product = Product::factory()->for($seller)->create();

        $response = $this->actingAs($user)->post('/products/' . $product->id . '/favorite');

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_user_cannot_add_the_same_product_twice(): void
    {
        $user = User::factory()->create();
        $seller = User::factory()->create();
        $product = Product::factory()->for($seller)->create();

        $this->actingAs($user)->post('/products/' . $product->id . '/favorite');
        $this->actingAs($user)->post('/products/' . $product->id . '/favorite');

        $this->assertDatabaseCount('favorites', 1);
    }

    public function test_user_can_remove_a_product_from_favorites(): void
    {
        $user = User::factory()->create();
        $seller = User::factory()->create();
        $product = Product::factory()->for($seller)->create();
        $user->favorites()->create(['product_id' => $product->id]);

        $response = $this->actingAs($user)->delete('/products/' . $product->id . '/favorite');

        $response->assertSessionHas('success');
        $this->assertDatabaseCount('favorites', 0);
    }

    public function test_favorites_page_lists_only_favorited_products(): void
    {
        $user = User::factory()->create();
        $seller = User::factory()->create();
        $favorite = Product::factory()->for($seller)->create(['name' => 'Manis favorīts']);
        $other = Product::factory()->for($seller)->create(['name' => 'Nav favorīts']);
        $user->favorites()->create(['product_id' => $favorite->id]);

        $response = $this->actingAs($user)->get('/favorites');

        $response->assertOk();
        $response->assertSee('Manis favorīts');
        $response->assertDontSee('Nav favorīts');
    }

    public function test_favorites_are_deleted_when_product_is_deleted(): void
    {
        $user = User::factory()->create();
        $seller = User::factory()->create();
        $product = Product::factory()->for($seller)->create();
        $user->favorites()->create(['product_id' => $product->id]);

        $product->delete();

        $this->assertDatabaseMissing('favorites', ['product_id' => $product->id]);
        $this->assertDatabaseCount('favorites', 0);
    }
}