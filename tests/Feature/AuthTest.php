<?php


namespace Tests\Feature;


use App\Constants\ErrorMessagesConstant;
use App\Mails\ForgotPasswordMail;
use App\Mails\VerifyMail;
use App\Models\User;
use App\Models\VerificationToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthTest extends TestCase {
    public function testLogin()
    {
        $user = User::factory()->createOne(["password" => Hash::make("password")]);

        $this->postJson("/api/auth/login", [
            "username" => $user->email,
            "password" => "password"
        ])->assertStatus(200)->assertJsonStructure(['access_token']);
    }

    public function testLoginNotVerified()
    {
        $user = User::factory()->createOne(["password" => Hash::make("password"), 'is_verified' => false]);

        $this->postJson("/api/auth/login", [
            "username" => $user->email,
            "password" => "password"
        ])->assertStatus(403)->assertJsonPath('message', ErrorMessagesConstant::NOT_VERIFIED_EMAIL);
    }

    public function testLoginWrongPassword()
    {
        $user = User::factory()->createOne(["password" => Hash::make("password")]);

        $this->postJson("/api/auth/login", [
            "username" => $user->email,
            "password" => "wrong_password"
        ])->assertStatus(401)->assertJsonPath('message', ErrorMessagesConstant::WRONG_CREDENTIALS);
    }

    public function testLogout()
    {
        $token = $this->login();

        $this->getJson("/api/auth/logout", [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(200);
    }

    public function testRefresh()
    {
        $token = $this->login();

        $this->getJson("/api/auth/refresh-token", [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(200)->assertJsonStructure(['access_token']);
    }

    public function testForgotPassword()
    {
        $user = User::factory()->createOne();
        $mail = Mail::fake();

        $this->postJson("/api/auth/forgot-password", [
           "email" => $user->email
        ])->assertStatus(200);

        $mail->assertSent(ForgotPasswordMail::class, function($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function testForgotPasswordWrongMail()
    {
        $mail = Mail::fake();

        $this->postJson("/api/auth/forgot-password", [
            "email" => 'test@test.com'
        ])->assertStatus(200);

        $mail->assertNothingSent();
    }

    public function testResetPassword()
    {
        $user = User::factory()->createOne();
        $token = Str::random(10);
        DB::table('password_resets')->insert([
            'token' => $token,
            'email' => $user->email
        ]);

        $this->postJson("/api/auth/reset-password", [
            "token" => $token,
            "password" => "password",
            "password_confirmation" => "password"
        ])->assertStatus(200);

        $u = User::find($user->id);
        // HASHES are different
        $this->assertNotEquals($user->password, $u->password);
    }

    public function testSendVerificationEmail()
    {
        $user = User::factory()->createOne();
        $mail = Mail::fake();

        $this->postJson("/api/auth/verify-email", [
            "email" => $user->email
        ])->assertStatus(200);

        $mail->assertSent(VerifyMail::class, function($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function testSendVerificationEmailNotExist()
    {
        $mail = Mail::fake();

        $this->postJson("/api/auth/verify-email", [
            "email" => "test@test.com"
        ])->assertStatus(400);

        $mail->assertNothingSent();
    }

    public function testVerifyEmail()
    {
        $user = User::factory()->createOne(["is_verified" => false]);
        $token = Str::random(10);
        (new VerificationToken([
            'email' => $user->email,
            'token' => $token,
            'valid_until' => Carbon::now()->addHours(2),
        ]))->save();

        $this->putJson("/api/auth/verify-email", [
            "token" => $token,
            "email" => $user->email
        ])->assertStatus(200);

        $u = User::find($user->id);
        $this->assertEquals(1, $u->is_verified);
    }

    public function testVerifyEmailTokenNotExists()
    {
        $user = User::factory()->createOne(["is_verified" => false]);
        $token = Str::random(10);


        $this->putJson("/api/auth/verify-email", [
            "token" => $token,
            "email" => $user->email
        ])->assertStatus(400);
    }

    public function testVerifyEmailUserNotExists()
    {
        $token = Str::random(10);


        $this->putJson("/api/auth/verify-email", [
            "token" => $token,
            "email" => "test@test.com"
        ])->assertStatus(400);
    }

    public function testUserRegistration()
    {
        $this->postJson("/api/auth/register-user", [
            "email" => "test@test.com",
            "password" => "password",
            "password_confirmation" => "password",
            "firstName" => $this->faker->firstName,
            "lastName" => $this->faker->lastName,
            "birthDate" => Carbon::now()->subYears(16)->format("Y-m-d"),
            "city" => $this->faker->city,
            "phone" => "0900000000",
            "email_confirmation" =>  "test@test.com",
            "terms_and_condition" => "on",
            "sex" => "m"
        ])->assertStatus(200);
    }

    public function testUserRegistrationUserAlreadyExists()
    {
        $user = User::factory()->createOne();
        $this->postJson("/api/auth/register-user", [
            "email" => $user->email,
            "password" => "password",
            "password_confirmation" => "password",
            "firstName" => $this->faker->firstName,
            "lastName" => $this->faker->lastName,
            "birthDate" => Carbon::now()->subYears(16)->format("Y-m-d"),
            "city" => $this->faker->city,
            "phone" => "0900000000",
            "email_confirmation" => $user->email,
            "terms_and_condition" => "on",
            "sex" => "m"
        ])->assertStatus(400)->assertJsonPath('message', ErrorMessagesConstant::USER_ALREADY_EXIST);
    }
}
