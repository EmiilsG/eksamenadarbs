<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_page_requires_auth(): void
    {
        $this->get('/profile/edit')->assertRedirect(route('login'));
    }

    public function test_update_requires_auth(): void
    {
        $this->put('/profile', ['name' => 'Jaunais vārds'])->assertRedirect(route('login'));
    }

    public function test_user_can_view_edit_page(): void
    {
        $user = User::factory()->create(['name' => 'Jānis Bērziņš']);

        $response = $this->actingAs($user)->get('/profile/edit');

        $response->assertOk();
        $response->assertSee('Rediģēt profilu');
        $response->assertSee('Jānis Bērziņš');
    }

    public function test_user_can_update_name_and_bio(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'Jaunais Vārds',
            'bio' => 'Sveiki, es pārdodu lietotas lietas!',
        ]);

        $response->assertRedirect(route('profile'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Jaunais Vārds',
            'bio' => 'Sveiki, es pārdodu lietotas lietas!',
        ]);
    }

    public function test_bio_is_optional(): void
    {
        $user = User::factory()->create(['bio' => 'Vecais apraksts']);

        $this->actingAs($user)->put('/profile', ['name' => $user->name, 'bio' => null]);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'bio' => null]);
    }

    public function test_name_is_required(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/profile', ['name' => '', 'bio' => 'x'])
            ->assertSessionHasErrors('name');
    }

    public function test_user_can_update_profile_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->actingAs($user)->put('/profile', [
            'name' => $user->name,
            'profile_image' => $file,
        ])->assertRedirect(route('profile'));

        $user->refresh();
        $this->assertNotNull($user->profile_image);
        Storage::disk('public')->assertExists($user->profile_image);
    }

    public function test_old_profile_image_is_deleted_when_replaced(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('profiles/old.jpg', 'fake');
        $user = User::factory()->create(['profile_image' => 'profiles/old.jpg']);

        $file = UploadedFile::fake()->image('new.jpg');

        $this->actingAs($user)->put('/profile', [
            'name' => $user->name,
            'profile_image' => $file,
        ]);

        $user->refresh();
        Storage::disk('public')->assertMissing('profiles/old.jpg');
        Storage::disk('public')->assertExists($user->profile_image);
    }

    public function test_http_profile_image_is_safely_replaced(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['profile_image' => 'https://i.pravatar.cc/300?img=1']);

        $file = UploadedFile::fake()->image('new.jpg');

        $this->actingAs($user)->put('/profile', [
            'name' => $user->name,
            'profile_image' => $file,
        ])->assertRedirect(route('profile'));

        $user->refresh();
        $this->assertStringStartsWith('profiles/', $user->profile_image);
        Storage::disk('public')->assertExists($user->profile_image);
    }
}