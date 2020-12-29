<?php


namespace App\Repositories;


use App\Constants\TableConstants;
use App\Models\NewsletterSubs;
use App\Models\Profile;
use App\Models\VerificationToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserRepository extends Repository
{
    function saveResetPasswordToken($token, $email)
    {
        return DB::table('password_resets')->insert([
            'token' => $token,
            'email' => $email,
        ]);
    }

    function findResetPasswordToken($token)
    {
        return DB::table('password_resets')->where('token', $token)->first();
    }

    function updateUser($data, $id)
    {
        return User::where('id', $id)->update($data);
    }

    function updateUserProfile($data, $userId)
    {
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

    public function list($size, $filter)
    {

        $query = DB::table(TableConstants::USERS)
            ->join(TableConstants::PROFILES,
                TableConstants::PROFILES.'.user_id',
                TableConstants::USERS.'.id');

        return $this->addWhereForFilter($query, $filter, [
            'profiles.first_name',
            'profiles.last_name',
            'profiles.birth_date',
            'profiles.phone',
            'profiles.city',
            'profiles.admin_note',
            'users.email',
        ])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('email')
            ->select([
                'profiles.first_name',
                'profiles.last_name',
                'profiles.birth_date',
                'profiles.phone',
                'profiles.city',
                'profiles.admin_note',
                'users.id',
                'users.email',
                'users.is_admin',
                'users.is_writer',
                DB::raw('(select count(*) from volunteers where users.id = volunteers.user_id and volunteers.was_on_event = 1 ) as volunteer_count'),
                DB::raw('(select count(*) from participants where users.id = participants.user_id and participants.was_on_event = 1 ) as participant_count'),
            ])->paginate($size);

    }

    public function findUser($userId)
    {
        return DB::table(TableConstants::USERS)
            ->find($userId);
    }

    public function findUserWithProfile($userId)
    {
        return DB::table(TableConstants::USERS)
            ->join(TableConstants::PROFILES, TableConstants::PROFILES.'.user_id', TableConstants::USERS.'.id')
            ->where(TableConstants::USERS.'.id',$userId)
            ->first();
    }


    public function registerToNewsLetter($email)
    {
        $foundedEmail = NewsletterSubs::where('email', $email);

        if (empty($foundedEmail)) {
            $newSub = new NewsletterSubs([
                'email' => $email,
                'active' => true
            ]);

            $newSub->save();
        } else {
            $foundedEmail
                ->update([
                    'active' => true
                ]);
        }
    }

    public function addVerificationEmailToken($email, string $token)
    {
        $token = new VerificationToken([
            'email' => $email,
            'token' => $token,
            'valid_until' => Carbon::now()->addHours(2)
        ]);

        return $token->save();
    }

    public function existVerificationEmailToken($email, $token)
    {
        return VerificationToken::all()
            ->where('token', $token)
            ->where('email', $email)
            ->first();
    }

    public function verifyUser($userId)
    {
        return User::where('id', $userId)->update([
            'is_verified' => true
        ]);
    }
}
