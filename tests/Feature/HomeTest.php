<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class HomeTest extends TestCase
{
    public function test_should_redirect_to_login_when_not_logged_in_at_root()
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
        $this->assertFalse(Auth::check());
    }

    public function test_should_redirect_to_login_when_not_logged_in_at_home()
    {
        $response = $this->get('/home');

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
        $this->assertFalse(Auth::check());
    }

    public function test_should_open_home_page_when_already_logged_in_at_root()
    {
        $u = User::factory()->create();
        $response = $this->actingAs($u)
            ->get('/');

        $response->assertStatus(200);
        $this->assertTrue(Auth::check());
    }

    public function test_should_open_home_page_when_already_logged_in_at_home()
    {
        $u = User::factory()->create();
        $response = $this->actingAs($u)
            ->get('/home');

        $response->assertStatus(200);
        $this->assertTrue(Auth::check());
    }

    public function test_should_see_page_title_when_not_not_logged_in()
    {
        $response = $this->followingRedirects()->get('/');

        $response->assertStatus(200);
        $response->assertSee("HNScraper");


    }

    public function test_should_see_page_title_when_already_logged_in()
    {
        $u = User::factory()->create();
        $response = $this->actingAs($u)
            ->get('/');

        $response->assertStatus(200);
        $response->assertSee("HNScraper");
    }

}
