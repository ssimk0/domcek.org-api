<?php

namespace Tests\Feature;

use App\Mails\ResetPasswordMail;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserTest extends TestCase {

    public function testUserList()
    {
        Profile::factory(20)->create();
        $response = $this->getJson('/api/secure/admin/users', $this->getAuthHeader());

        $response->assertStatus(200)
            ->assertJsonPath('total', 21)
            ->assertJsonPath('current_page', 1);
    }

    public function testUserListSearchByEmail()
    {
        $profiles = Profile::factory(5)->create();
        $token = $this->login(true);
        $response = $this->getJson('/api/secure/admin/users?filter=' . $profiles[0]->user->email, $this->getAuthHeader($token));

        $response->assertStatus(200)
            ->assertJsonPath('total', 1)
            ->assertJsonPath('current_page', 1);
    }

    public function testUserListSearchByName()
    {
        Profile::factory(5)->create();
        $token = $this->login(true);
        $response = $this->getJson('/api/secure/admin/users?filter=Admin Domcek', $this->getAuthHeader($token));

        $response->assertStatus(200)
            ->assertJsonPath('total', 1)
            ->assertJsonPath('current_page', 1);
    }

    public function testUserListSearchByPhone()
    {
        $profiles = Profile::factory(5)->create();
        $token = $this->login(true);
        $response = $this->getJson('/api/secure/admin/users?filter=' . $profiles[0]->phone, $this->getAuthHeader($token));

        $response->assertStatus(200)
            ->assertJsonPath('total', 1)
            ->assertJsonPath('current_page', 1);
    }

    public function testUserDetail()
    {
        $token = $this->login(true);
        $response = $this->getJson('/api/secure/user', $this->getAuthHeader($token));

        $response->assertStatus(200)
            ->assertJsonPath('admin', true)
            ->assertJsonPath('email', "test@test.com");
    }

    public function testUserChangePassword()
    {
        $token = $this->login();
        $user = Auth::user();
        $user->password = \Hash::make('passwordold');
        $user->save();
        $response = $this->putJson('/api/secure/user/change-password', [
            "oldPassword" => "passwordold",
            "password" => "password",
            "password_confirmation" => "password"
        ], $this->getAuthHeader($token));

        $response->assertStatus(200);

        $u = User::find($user->id);

        $this->assertNotEquals($u->password, $user->password);
    }

    public function testUserChangePasswordWrongOldPass()
    {
        $token = $this->login();
        $user = Auth::user();
        $user->password = \Hash::make('passwordold');
        $user->save();
        $response = $this->putJson('/api/secure/user/change-password', [
            "oldPassword" => "wrong",
            "password" => "password",
            "password_confirmation" => "password"
        ], $this->getAuthHeader($token));

        $response->assertStatus(400);
    }

    public function testUserUpdateUserProfile()
    {
        $token = $this->login();
        $user = Auth::user();

        $response = $this->putJson('/api/secure/user', [
            "city" => "city",
            "phone" => "0901000000",
            "lastName" => "newLastName",
        ], $this->getAuthHeader($token));

        $response->assertStatus(200);

        $u = User::find($user->id);

        $this->assertEquals("city", $u->profile->city);
        $this->assertEquals("0901000000", $u->profile->phone);
        $this->assertEquals("newLastName", $u->profile->last_name);
    }

    public function testUserEditByAdmin()
    {
        $token = $this->login(true);
        $user = Auth::user();

        $response = $this->putJson('/api/secure/admin/users/' . $user->id, [
            "firstName" => "newFirstName",
            "isAdmin" => true,
            "isEditor" => true,
            "email" => "test2@test.com"
        ], $this->getAuthHeader($token));

        $response->assertStatus(200);

        $u = User::find($user->id);

        $this->assertEquals("newFirstName", $u->profile->first_name);
        $this->assertEquals("test2@test.com", $u->email);
        $this->assertEquals(true, $u->is_writer);
    }

    public function testUserDetailAdmin()
    {
        $token = $this->login(true);
        $user = Auth::user();

        $response = $this->getJson('/api/secure/admin/users/' . $user->id, $this->getAuthHeader($token));

        $response->assertStatus(200)
            ->assertJsonPath("profile.first_name", $user->profile->first_name)
            ->assertJsonPath("email", $user->email);
    }

    public function testUserDetailResetPassword()
    {
        $token = $this->login(true);
        $user = Auth::user();
        Mail::fake();

        $response = $this->putJson('/api/secure/admin/users/' . $user->id . "/reset-password", [], $this->getAuthHeader($token));

        $response->assertStatus(200);

        Mail::assertSent(ResetPasswordMail::class, function($mail) {
            return $mail->hasTo("test@test.com");
        });

        $u = User::find($user->id);

        $this->assertNotEquals($user->password, $u->password);
    }
}
