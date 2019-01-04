<?php


namespace App\Repositories;


use App\Models\Profile;
use App\User;
use Illuminate\Support\Facades\DB;

class UserRepository extends Repository
{
    function saveResetPasswordToken($token, $email) {
        return DB::table('password_resets')->insert([
            'token' => $token,
            'email' => $email
        ]);
    }

    function findResetPasswordToken($token) {
        return DB::table('password_resets')->where('token', $token)->first();
    }

    function updateUser($data, $id) {
        return User::where('id', $id)->update($data);
    }

    function updateUserProfile($data, $userId) {
        Profile::where('user_id', $userId)->update($data);
        return Profile::where('user_id', $userId)->first();
    }

    function findUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    function getUserProfile($id)
    {
        return Profile::where('user_id', $id)->first();
    }

    function createUser(array $data)
    {
        $user = new User();
        $user->email = $data['email'];
        $user->password = $data['password'];
        $user->avatar = $data['avatar'];
        $user->save();
        return $user;
    }

    function createUserProfile(array $profileData)
    {
        $profile = new Profile($profileData);

        $profile->save();
    }
}
