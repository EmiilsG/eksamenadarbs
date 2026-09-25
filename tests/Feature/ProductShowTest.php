<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_product_page(): void
    {
        $product = Product::factory()->create(['name' => 'Velocipēds Scott']);

        $response = $this->get('/products/' . $product->id);

        $response->assertOk();
        $response->assertSee('Velocipēds Scott');
        $response->assertSee(number_format($product->price, 2));
    }

    public function test_guest_is_asked_to_login_when_favoriting_from_product_page(): void
    {
        $product = Product::factory()->create();

        $response = $this->get('/products/' . $product->id);

        $response->assertOk();
        $response->assertSee(route('login'));
        $response->assertDontSee(route('favorites.store', $product), false);
    }

    public function test_product_page_shows_seller_details(): void
    {
        $seller = User::factory()->create(['name' => 'Laura Kalniņa']);
        $product = Product::factory()->for($seller)->create(['name' => 'Sony PS5']);

        $response = $this->get('/products/' . $product->id);

        $response->assertOk();
        $response->assertSee('Laura Kalniņa');
        $response->assertSee(route('profile.user', $seller));
    }

    public function test_product_page_shows_latest_three_seller_reviews(): void
    {
        $seller = User::factory()->create();
        $product = Product::factory()->for($seller)->create();

        foreach (range(1, 4) as $i) {
            $review = new Review([
                'reviewee_id' => $seller->id,
                'reviewer_id' => User::factory()->create()->id,
                'rating' => 5,
                'comment' => 'Atsauksme numur ' . $i,
            ]);
            $review->timestamps = false;
            $review->created_at = now()->subDays(10 - $i);
            $review->save();
        }

        $response = $this->get('/products/' . $product->id);

        $response->assertOk();
        $response->assertSee('Atsauksme numur 4');
        $response->assertSee('Atsauksme numur 2');
        $response->assertDontSee('Atsauksme numur 1');
    }

    public function test_product_page_lists_other_products_from_same_seller(): void
    {
        $seller = User::factory()->create();
        $other = Product::factory()->for($seller)->create(['name' => 'Cits produkts']);
        $foreign = Product::factory()->create(['name' => 'Cita pārdevēja produkts']);
        $product = Product::factory()->for($seller)->create(['name' => 'Pašreizējais produkts']);

        $response = $this->get('/products/' . $product->id);

        $response->assertOk();
        $response->assertSee('Cits produkts');
        $response->assertDontSee('Cita pārdevēja produkts');
    }

    public function test_owner_sees_edit_and_delete_buttons(): void
    {
        $owner = User::factory()->create();
        $product = Product::factory()->for($owner)->create();

        $response = $this->actingAs($owner)->get('/products/' . $product->id);

        $response->assertOk();
        $response->assertSee(route('products.edit', $product));
    }

    public function test_guest_does_not_see_edit_buttons(): void
    {
        $product = Product::factory()->create();

        $response = $this->get('/products/' . $product->id);

        $response->assertOk();
        $response->assertDontSee(route('products.edit', $product));
    }

    public function test_favorite_state_is_reflected_on_product_page(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->get('/products/' . $product->id)
            ->assertSee('Pievienot favorītiem');

        $user->favorites()->create(['product_id' => $product->id]);

        $this->actingAs($user)->get('/products/' . $product->id)
            ->assertSee('Noņemt no favorītiem');
    }

    public function test_product_page_renders_seeded_shape_with_remote_image_and_no_bio(): void
    {
        $seller = User::factory()->create([
            'profile_image' => 'https://i.pravatar.cc/300?img=12',
        ]);
        $product = Product::factory()->for($seller)->create([
            'image' => 'https://picsum.photos/seed/product1/600/400',
            'category' => 'Elektronika',
        ]);

        $response = $this->get('/products/' . $product->id);

        $response->assertOk();
        $response->assertSee('https://picsum.photos/seed/product1/600/400', false);
        $response->assertSee('https://i.pravatar.cc/300?img=12', false);
        $response->assertSee('Elektronika');
    }

    public function test_missing_product_returns_404(): void
    {
        $this->get('/products/999999')->assertNotFound();
    }
}
