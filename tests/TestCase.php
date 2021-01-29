<?php
namespace Tests;

use App\Models\User;
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
        $user = new User();
        $user->id = 1;
        $user->is_admin = $admin;
        $user->is_writer = $editor;
        $user->email = 'test@test.com';

        return Auth::login($user);
    }
}
