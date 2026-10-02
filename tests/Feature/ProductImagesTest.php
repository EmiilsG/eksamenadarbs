<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_can_be_created_with_multiple_images(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/products', [
            'name' => 'iPhone 13',
            'description' => 'Lietots iPhone 13',
            'price' => 649,
            'category' => 'Elektronika',
            'images' => [
                UploadedFile::fake()->create('first.jpg', 10, 'image/jpeg'),
                UploadedFile::fake()->create('second.jpg', 10, 'image/jpeg'),
                UploadedFile::fake()->create('third.jpg', 10, 'image/jpeg'),
            ],
        ]);

        $response->assertRedirect(route('products.index'));

        $product = Product::firstWhere('name', 'iPhone 13');

        $this->assertCount(3, $product->images);
        $this->assertSame($product->images->first()->path, $product->image);
        $this->assertSame([0, 1, 2], $product->images->pluck('position')->all());

        foreach ($product->images as $image) {
            Storage::disk('public')->assertExists($image->path);
        }
    }

    public function test_product_can_be_created_without_images(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->post('/products', [
            'name' => 'Produkts bez attēliem',
            'description' => 'Apraksts',
            'price' => 10,
        ])->assertRedirect(route('products.index'));

        $product = Product::firstWhere('name', 'Produkts bez attēliem');

        $this->assertNull($product->image);
        $this->assertCount(0, $product->images);
    }

    public function test_more_images_than_the_limit_are_rejected(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $images = [];
        for ($i = 0; $i <= Product::MAX_IMAGES; $i++) {
            $images[] = UploadedFile::fake()->create('image' . $i . '.jpg', 10, 'image/jpeg');
        }

        $this->actingAs($user)->post('/products', [
            'name' => 'Pārāk daudz attēlu',
            'description' => 'Apraksts',
            'price' => 10,
            'images' => $images,
        ])->assertSessionHasErrors('images');

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('product_images', 0);
    }

    public function test_non_image_files_are_rejected(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->post('/products', [
            'name' => 'Produkts ar dokumentu',
            'description' => 'Apraksts',
            'price' => 10,
            'images' => [UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf')],
        ])->assertSessionHasErrors('images.0');

        $this->assertDatabaseCount('products', 0);
    }

    public function test_owner_can_append_images_without_changing_product_data(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $product = Product::factory()->for($owner)->create(['name' => 'Sony PS5', 'price' => 499]);

        $this->actingAs($owner)->post('/products/' . $product->id . '/images', [
            'images' => [
                UploadedFile::fake()->create('a.jpg', 10, 'image/jpeg'),
                UploadedFile::fake()->create('b.jpg', 10, 'image/jpeg'),
            ],
        ])->assertRedirect(route('products.edit', $product));

        $product->refresh()->load('images');

        $this->assertCount(2, $product->images);
        $this->assertSame('Sony PS5', $product->name);
        $this->assertEquals(499, $product->price);
    }

    public function test_append_images_respects_remaining_slots(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $product = Product::factory()->for($owner)->create(['image' => 'products/a.jpg']);

        for ($i = 0; $i < Product::MAX_IMAGES; $i++) {
            $product->images()->create(['path' => 'products/' . $i . '.jpg', 'position' => $i]);
        }

        $this->assertSame(0, $product->remainingImageSlots());

        $this->actingAs($owner)->post('/products/' . $product->id . '/images', [
            'images' => [UploadedFile::fake()->create('extra.jpg', 10, 'image/jpeg')],
        ])->assertSessionHasErrors('images');

        $this->assertCount(Product::MAX_IMAGES, $product->fresh()->images);
    }

    public function test_guest_cannot_append_images(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create();

        $this->post('/products/' . $product->id . '/images', [
            'images' => [UploadedFile::fake()->create('a.jpg', 10, 'image/jpeg')],
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('product_images', 0);
    }

    public function test_owner_can_promote_an_image_to_main(): void
    {
        $owner = User::factory()->create();
        $product = Product::factory()->for($owner)->create(['image' => 'products/first.jpg']);
        $first = $product->images()->create(['path' => 'products/first.jpg', 'position' => 0]);
        $second = $product->images()->create(['path' => 'products/second.jpg', 'position' => 1]);

        $this->actingAs($owner)->post('/products/' . $product->id . '/images/' . $second->id . '/main')
            ->assertRedirect(route('products.edit', $product));

        $product->refresh()->load('images');

        $this->assertSame('products/second.jpg', $product->image);
        $this->assertSame([$second->id, $first->id], $product->images->pluck('id')->all());
        $this->assertSame([0, 1], $product->images->pluck('position')->all());
    }

    public function test_non_owner_cannot_promote_an_image(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $product = Product::factory()->for($owner)->create(['image' => 'products/first.jpg']);
        $product->images()->create(['path' => 'products/first.jpg', 'position' => 0]);
        $second = $product->images()->create(['path' => 'products/second.jpg', 'position' => 1]);

        $this->actingAs($other)->post('/products/' . $product->id . '/images/' . $second->id . '/main')
            ->assertForbidden();

        $this->assertSame('products/first.jpg', $product->fresh()->image);
    }

    public function test_owner_can_delete_an_image(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $product = Product::factory()->for($owner)->create(['image' => 'products/first.jpg']);
        Storage::disk('public')->put('products/first.jpg', 'fake');
        Storage::disk('public')->put('products/second.jpg', 'fake');
        $product->images()->create(['path' => 'products/first.jpg', 'position' => 0]);
        $second = $product->images()->create(['path' => 'products/second.jpg', 'position' => 1]);

        $this->actingAs($owner)->delete('/products/' . $product->id . '/images/' . $second->id)
            ->assertRedirect(route('products.edit', $product));

        $this->assertDatabaseMissing('product_images', ['id' => $second->id]);
        Storage::disk('public')->assertMissing('products/second.jpg');
        $this->assertSame('products/first.jpg', $product->fresh()->image);
    }

    public function test_deleting_main_image_promotes_another_one(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $product = Product::factory()->for($owner)->create(['image' => 'products/first.jpg']);
        Storage::disk('public')->put('products/first.jpg', 'fake');
        Storage::disk('public')->put('products/second.jpg', 'fake');
        $first = $product->images()->create(['path' => 'products/first.jpg', 'position' => 0]);
        $product->images()->create(['path' => 'products/second.jpg', 'position' => 1]);

        $this->actingAs($owner)->delete('/products/' . $product->id . '/images/' . $first->id);

        $this->assertDatabaseMissing('product_images', ['id' => $first->id]);
        $this->assertSame('products/second.jpg', $product->fresh()->image);
    }

    public function test_image_from_another_product_returns_404(): void
    {
        $owner = User::factory()->create();
        $product = Product::factory()->for($owner)->create();
        $foreignImage = ProductImage::create(['product_id' => Product::factory()->create()->id, 'path' => 'products/foreign.jpg']);

        $this->actingAs($owner)->delete('/products/' . $product->id . '/images/' . $foreignImage->id)
            ->assertNotFound();

        $this->assertDatabaseHas('product_images', ['id' => $foreignImage->id]);
    }

    public function test_images_are_deleted_when_product_is_deleted(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $product = Product::factory()->for($owner)->create(['image' => 'products/first.jpg']);
        Storage::disk('public')->put('products/first.jpg', 'fake');
        Storage::disk('public')->put('products/second.jpg', 'fake');
        $product->images()->create(['path' => 'products/first.jpg', 'position' => 0]);
        $product->images()->create(['path' => 'products/second.jpg', 'position' => 1]);

        $this->actingAs($owner)->delete('/products/' . $product->id)
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseCount('product_images', 0);
        Storage::disk('public')->assertMissing('products/first.jpg');
        Storage::disk('public')->assertMissing('products/second.jpg');
    }

    public function test_create_page_offers_multiple_image_upload(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/products/create');

        $response->assertOk();
        $response->assertSee('name="images[]"', false);
        $response->assertSee('multiple', false);
    }

    public function test_owner_edit_page_lists_images_with_actions(): void
    {
        $owner = User::factory()->create();
        $product = Product::factory()->for($owner)->create(['image' => 'products/a.jpg']);
        $first = $product->images()->create(['path' => 'products/a.jpg', 'position' => 0]);
        $second = $product->images()->create(['path' => 'products/b.jpg', 'position' => 1]);

        $response = $this->actingAs($owner)->get('/products/' . $product->id . '/edit');

        $response->assertOk();
        $response->assertSee('Galvenais attēls');
        $response->assertSee(route('products.images.destroy', [$product, $first]));
        $response->assertSee(route('products.images.main', [$product, $second]));
        $response->assertSee(route('products.images.store', $product));
        $response->assertSee('2 / ' . Product::MAX_IMAGES);
    }

    public function test_non_owner_edit_page_is_forbidden(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $product = Product::factory()->for($owner)->create(['image' => 'products/a.jpg']);
        $product->images()->create(['path' => 'products/a.jpg', 'position' => 0]);

        $this->actingAs($other)->get('/products/' . $product->id . '/edit')
            ->assertForbidden();

        $this->actingAs($other)->delete('/products/' . $product->id . '/images/' . $product->images()->value('id'))
            ->assertForbidden();
    }

    public function test_product_page_shows_gallery_thumbnails(): void
    {
        $product = Product::factory()->create(['image' => 'https://picsum.photos/seed/main/600/400']);
        $product->images()->create(['path' => 'https://picsum.photos/seed/main/600/400', 'position' => 0]);
        $product->images()->create(['path' => 'https://picsum.photos/seed/side/600/400', 'position' => 1]);

        $response = $this->get('/products/' . $product->id);

        $response->assertOk();
        $response->assertSee('https://picsum.photos/seed/side/600/400', false);
        $response->assertSee('2 attēl(-i)');
    }

    public function test_product_without_gallery_has_no_thumbnail_strip(): void
    {
        $product = Product::factory()->create(['image' => 'https://picsum.photos/seed/only/600/400']);

        $response = $this->get('/products/' . $product->id);

        $response->assertOk();
        $response->assertDontSee('id="gallery"', false);
        $response->assertSee('1 attēl(-i)');
    }
}
