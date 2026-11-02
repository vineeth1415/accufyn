<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Locality;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCityTest extends TestCase
{
    public function test_admin_can_view_cities_index_page(): void
    {
        $admin = User::where('role', 'ADMIN')->first();

        $response = $this->actingAs($admin)->get('/admin/cities');
        $response->assertStatus(200);
        $response->assertSee('Popular Cities & Location Showcase');
        $response->assertSee('Explore Popular Cities Showcase Directory');
        $response->assertSee('Guwahati');
        $response->assertSee('Mumbai');
    }

    public function test_non_admin_cannot_access_cities_management(): void
    {
        $guestResponse = $this->get('/admin/cities');
        $guestResponse->assertRedirect('/login');

        $buyer = User::where('role', 'BUYER')->first();
        $buyerResponse = $this->actingAs($buyer)->get('/admin/cities');
        $buyerResponse->assertStatus(403);

        $staff = User::where('role', 'STAFF')->first();
        $staffResponse = $this->actingAs($staff)->get('/admin/cities');
        $staffResponse->assertStatus(403);
    }

    public function test_admin_can_upload_city_image_and_update_details(): void
    {
        Storage::fake('public');
        $admin = User::where('role', 'ADMIN')->first();
        $city = City::first();
        $originalName = $city->name;
        $newName = $originalName . ' Metro ' . rand(100, 999);

        $fakeImage = UploadedFile::fake()->image('guwahati_skyline.jpg', 800, 600);

        $response = $this->actingAs($admin)->put("/admin/cities/{$city->id}", [
            'name' => $newName,
            'state' => 'Assam, North-East',
            'is_popular' => 1,
            'sort_order' => 1,
            'image_file' => $fakeImage,
        ]);

        $response->assertRedirect('/admin/cities');
        $response->assertSessionHas('success');

        $city->refresh();
        $this->assertEquals($newName, $city->name);
        $this->assertEquals('Assam, North-East', $city->state);
        $this->assertTrue($city->is_popular);
        $this->assertEquals(1, $city->sort_order);
        $this->assertNotEmpty($city->image_url);
        $this->assertStringContainsString('/storage/cities/', $city->image_url);

        // Verify storage file exists
        $filename = basename(parse_url($city->image_url, PHP_URL_PATH));
        Storage::disk('public')->assertExists('cities/' . $filename);

        // Also verify homepage shows this city and image
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Explore Popular Cities');
        $homeResponse->assertSee($newName);
        $homeResponse->assertSee($filename);

        // Clean up name
        $city->update(['name' => $originalName]);
        Locality::where('city', $newName)->update(['city' => $originalName]);
        Property::where('city', $newName)->update(['city' => $originalName]);
    }

    public function test_admin_can_remove_custom_image_to_revert_to_default(): void
    {
        $admin = User::where('role', 'ADMIN')->first();
        $city = City::first();

        $city->update(['image_url' => 'https://example.com/custom-city.jpg']);

        $response = $this->actingAs($admin)->put("/admin/cities/{$city->id}", [
            'name' => $city->name,
            'state' => $city->state,
            'remove_image' => 1,
        ]);

        $response->assertRedirect('/admin/cities');
        $city->refresh();
        $this->assertNull($city->image_url);
        $this->assertNotEmpty($city->display_image_url);
        $this->assertStringContainsString('images.unsplash.com', $city->display_image_url);
    }

    public function test_admin_can_toggle_popular_status(): void
    {
        $admin = User::where('role', 'ADMIN')->first();
        $city = City::first();
        $initialStatus = $city->is_popular;

        $response = $this->actingAs($admin)->post("/admin/cities/{$city->id}/toggle-popular");
        $response->assertRedirect('/admin/cities');

        $city->refresh();
        $this->assertEquals(!$initialStatus, $city->is_popular);
    }

    public function test_admin_can_create_new_city_with_uploaded_image(): void
    {
        Storage::fake('public');
        $admin = User::where('role', 'ADMIN')->first();
        $fakeImage = UploadedFile::fake()->image('chennai_beach.jpg', 600, 800);

        $cityName = 'Chennai ' . rand(100, 999);

        $response = $this->actingAs($admin)->post('/admin/cities', [
            'name' => $cityName,
            'state' => 'Tamil Nadu',
            'is_popular' => 1,
            'sort_order' => 2,
            'image_file' => $fakeImage,
        ]);

        $response->assertRedirect('/admin/cities');
        $response->assertSessionHas('success');

        $city = City::where('name', $cityName)->first();
        $this->assertNotNull($city);
        $this->assertEquals('Tamil Nadu', $city->state);
        $this->assertStringContainsString('/storage/cities/', $city->image_url);

        // Delete test city
        $deleteResponse = $this->actingAs($admin)->delete("/admin/cities/{$city->id}");
        $deleteResponse->assertRedirect('/admin/cities');
        $this->assertNull(City::where('name', $cityName)->first());
    }
}
