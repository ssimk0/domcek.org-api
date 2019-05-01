<?php


namespace App\Services;

use App\Repositories\TokenRepository;


class TokenService extends Service
{
    private $repository;

    public function __construct(TokenRepository $repository)
    {
        $this->repository = $repository;
    }

    public function checkToken($type, $token)
    {
        return $this->repository->tokenExists($type, $token);
    }
}