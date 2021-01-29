<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

abstract class TestCase extends Laravel\Lumen\Testing\TestCase
{
    protected $user;

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

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
