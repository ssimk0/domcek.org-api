<?php

use App\User;
use Illuminate\Support\Facades\Auth;

abstract class TestCase extends Laravel\Lumen\Testing\TestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }

    function login($admin = false, $editor = false, $reg = false)
    {
        $user = new User();
        $user->id = 1;
        $user->is_admin = $admin;
        $user->is_writer = $editor;
        $user->is_registration = $reg;
        return Auth::login($user);
    }
}
