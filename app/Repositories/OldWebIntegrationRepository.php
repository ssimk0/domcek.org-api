<?php
/**
 * Created by PhpStorm.
 * User: sebastiansimko
 * Date: 5.3.2019
 * Time: 16:29
 */

namespace App\Repositories;


use Illuminate\Support\Facades\DB;

class OldWebIntegrationRepository extends Repository
{
    function findOldUser($email)
    {
        return DB::table('users_old')
            ->where('email', $email)
            ->get(['user_id']);
    }

    function findOldEventRegistration($userId)
    {
        return DB::table('pilgrims')
            ->where('user_id', $userId)
            ->get(['action_id', 'real_role', 'was_on_act', 'role', 'note', 'payedDeposit', 'payedReg']);
    }
}