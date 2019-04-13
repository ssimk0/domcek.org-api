<?php

use App\User;
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
        return require __DIR__ . '/../bootstrap/app.php';
    }

    function login($admin = false, $editor = false, $reg = false)
    {
        $this->user = factory(User::class)->create();
        $this->user->update([
            'is_admin' => $admin,
            'is_writer' => $editor,
            'is_registration' => $reg,
        ]);
        return Auth::login($this->user);
    }
}
