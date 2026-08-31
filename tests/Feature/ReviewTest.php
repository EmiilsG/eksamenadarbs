<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_a_review(): void
    {
        $reviewer = User::factory()->create();
        $seller = User::factory()->create();

        $response = $this->actingAs($reviewer)->post('/profile/' . $seller->id . '/review', [
            'rating' => 5,
            'comment' => 'Lielisks pārdevējs!',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('reviews', [
            'reviewer_id' => $reviewer->id,
            'reviewee_id' => $seller->id,
            'rating' => 5,
            'comment' => 'Lielisks pārdevējs!',
        ]);
    }

    public function test_average_rating_is_updated(): void
    {
        $seller = User::factory()->create();
        $r1 = User::factory()->create();
        $r2 = User::factory()->create();

        $this->actingAs($r1)->post('/profile/' . $seller->id . '/review', ['rating' => 4, 'comment' => 'a']);
        $this->actingAs($r2)->post('/profile/' . $seller->id . '/review', ['rating' => 2, 'comment' => 'b']);

        $seller->refresh();
        $this->assertEquals(3.0, (float) $seller->average_rating);
        $this->assertEquals(2, $seller->reviews_count);
    }

    public function test_user_cannot_review_themselves(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/profile/' . $user->id . '/review', [
            'rating' => 5,
            'comment' => 'self',
        ]);

        $response->assertSessionHasErrors('rating');
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_validation_requires_rating_between_1_and_5(): void
    {
        $reviewer = User::factory()->create();
        $seller = User::factory()->create();

        $this->actingAs($reviewer)->post('/profile/' . $seller->id . '/review', [
            'rating' => 6,
            'comment' => 'x',
        ])->assertSessionHasErrors('rating');

        $this->actingAs($reviewer)->post('/profile/' . $seller->id . '/review', [
            'comment' => 'x',
        ])->assertSessionHasErrors('rating');
    }

    public function test_validation_requires_comment(): void
    {
        $reviewer = User::factory()->create();
        $seller = User::factory()->create();

        $this->actingAs($reviewer)->post('/profile/' . $seller->id . '/review', [
            'rating' => 5,
        ])->assertSessionHasErrors('comment');
    }

    public function test_review_requires_auth(): void
    {
        $seller = User::factory()->create();
        $this->post('/profile/' . $seller->id . '/review', [
            'rating' => 5,
            'comment' => 'x',
        ])->assertRedirect(route('login'));
    }
}
