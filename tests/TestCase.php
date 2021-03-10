<?php
namespace Tests;

use App\Models\Profile;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected $user;

    use CreatesApplication, DatabaseMigrations, WithFaker;

    public function login($admin = false, $editor = false, $reg = false)
    {
        $user = Profile::factory()->createOne();
        $user->user->is_admin = $admin;
        $user->user->is_writer = $editor;
        $user->user->email = 'test@test.com';
        $user->user->save();
        // to be able search by name
        $user->phone = "0900000000";
        $user->first_name = "Admin";
        $user->last_name = "Domcek";
        $user->save();

        return Auth::login($user->user);
    }

    public function getAuthHeader($token=null)
    {
        $token = $token ?? $this->login(true);
        return [
            'Authorization' => 'Bearer ' . $token,
        ];
    }
}
