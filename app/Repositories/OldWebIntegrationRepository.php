<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class OldWebIntegrationRepository extends Repository
{
    public function findOldUser($email)
    {
        return DB::table('users_old')
            ->where('email', $email)
            ->pluck('user_id');
    }

    public function findOldEventRegistration($userId)
    {
        return DB::table('pilgrims')
            ->where('user_id', $userId)
            ->get(['action_id', 'real_role', 'was_on_act', 'role', 'note', 'payedDeposit', 'payedReg']);
    }
}
