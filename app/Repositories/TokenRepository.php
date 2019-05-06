<?php


namespace App\Repositories;


use App\Constants\TableConstants;
use Illuminate\Support\Facades\DB;

class TokenRepository extends Repository
{

    public function tokenExists($type, $token)
    {
        return DB::table(TableConstants::AUTH_TOKEN)
            ->where('type', $type)
            ->where('token', $token)
            ->first();
    }
}