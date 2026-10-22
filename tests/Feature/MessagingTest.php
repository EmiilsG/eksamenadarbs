<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessagingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_viewing_messages(): void
    {
        $this->get('/messages')->assertRedirect(route('login'));
    }

    public function test_guest_cannot_start_a_conversation(): void
    {
        $seller = User::factory()->create();
        $product = Product::factory()->for($seller)->create();

        $this->post('/products/' . $product->id . '/message')->assertRedirect(route('login'));
        $this->assertDatabaseCount('conversations', 0);
    }

    public function test_buyer_can_start_a_conversation_from_product_page(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $product = Product::factory()->for($seller)->create();

        $response = $this->actingAs($buyer)->post('/products/' . $product->id . '/message');

        $conversation = Conversation::first();
        $this->assertNotNull($conversation);
        $this->assertSame($buyer->id, $conversation->buyer_id);
        $this->assertSame($seller->id, $conversation->seller_id);
        $this->assertSame($product->id, $conversation->product_id);

        $response->assertRedirect(route('conversations.show', $conversation));
    }

    public function test_conversation_is_not_duplicated_for_the_same_product(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $product = Product::factory()->for($seller)->create();

        $this->actingAs($buyer)->post('/products/' . $product->id . '/message');
        $this->actingAs($buyer)->post('/products/' . $product->id . '/message');

        $this->assertDatabaseCount('conversations', 1);
    }

    public function test_user_cannot_message_own_product(): void
    {
        $seller = User::factory()->create();
        $product = Product::factory()->for($seller)->create();

        $this->actingAs($seller)
            ->post('/products/' . $product->id . '/message')
            ->assertSessionHas('error');

        $this->assertDatabaseCount('conversations', 0);
    }

    public function test_user_can_start_a_conversation_from_seller_profile(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();

        $response = $this->actingAs($buyer)->post('/profile/' . $seller->id . '/message');

        $conversation = Conversation::first();
        $this->assertNotNull($conversation);
        $this->assertNull($conversation->product_id);

        $response->assertRedirect(route('conversations.show', $conversation));
    }

    public function test_profile_conversation_is_not_duplicated(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();

        $this->actingAs($buyer)->post('/profile/' . $seller->id . '/message');
        $this->actingAs($buyer)->post('/profile/' . $seller->id . '/message');

        $this->assertDatabaseCount('conversations', 1);
    }

    public function test_user_can_send_a_message_in_a_conversation(): void    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $product = Product::factory()->for($seller)->create();
        $conversation = Conversation::create([
            'product_id' => $product->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $response = $this->actingAs($buyer)->post('/messages/' . $conversation->id . '/reply', [
            'body' => 'Sveiki, vai produkts vēl pieejams?',
        ]);

        $response->assertRedirect(route('conversations.show', $conversation));
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'user_id' => $buyer->id,
            'body' => 'Sveiki, vai produkts vēl pieejams?',
        ]);
    }

    public function test_message_body_is_required(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $conversation = Conversation::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $this->actingAs($buyer)
            ->post('/messages/' . $conversation->id . '/reply', ['body' => ''])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('messages', 0);
    }

    public function test_outsider_cannot_read_or_reply_to_a_conversation(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $outsider = User::factory()->create();
        $conversation = Conversation::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $this->actingAs($outsider)->get('/messages/' . $conversation->id)->assertForbidden();
        $this->actingAs($outsider)->post('/messages/' . $conversation->id . '/reply', ['body' => 'Hajs'])->assertForbidden();

        $this->assertDatabaseCount('messages', 0);
    }

    public function test_conversation_page_shows_messages_for_participants(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $conversation = Conversation::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);
        $conversation->messages()->create([
            'user_id' => $buyer->id,
            'body' => 'Sveiki, pārdevēj!',
        ]);

        $response = $this->actingAs($seller)->get('/messages/' . $conversation->id);

        $response->assertOk();
        $response->assertSee('Sveiki, pārdevēj!');
    }

    public function test_opening_a_conversation_marks_messages_as_read(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $conversation = Conversation::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);
        $message = $conversation->messages()->create([
            'user_id' => $buyer->id,
            'body' => 'Sveiki',
        ]);

        $this->actingAs($seller)->get('/messages/' . $conversation->id)->assertOk();

        $this->assertNotNull($message->fresh()->read_at);
    }

    public function test_messages_page_lists_only_own_conversations(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $other = User::factory()->create();

        $mine = Conversation::create(['buyer_id' => $buyer->id, 'seller_id' => $seller->id]);
        Conversation::create(['buyer_id' => $other->id, 'seller_id' => $seller->id]);

        $response = $this->actingAs($buyer)->get('/messages');

        $response->assertOk();
        $response->assertSee($seller->name);
        $response->assertDontSee($other->name);
    }

    public function test_unread_badge_is_shown_for_unread_messages(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $conversation = Conversation::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);
        $conversation->messages()->create([
            'user_id' => $buyer->id,
            'body' => 'Sveiki',
        ]);

        $response = $this->actingAs($seller)->get('/products');

        $response->assertOk();
        $response->assertSee(route('conversations.index'));
    }

    public function test_conversation_is_deleted_with_the_product(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $product = Product::factory()->for($seller)->create();
        Conversation::create([
            'product_id' => $product->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $product->delete();

        $this->assertDatabaseCount('conversations', 0);
    }
}
