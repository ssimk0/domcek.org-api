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

    function login()
    {
        $user = new User();
        return Auth::login($user);
    }
}
