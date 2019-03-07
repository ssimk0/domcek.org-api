<?php

namespace App\Constants;


class ErrorMessagesConstant
{
    const BAD_REQUEST = 'BAD_REQUEST';
    const BAD_ATTEMPT = 'BAD_ATTEMPT';
    const NO_DATA = 'NO_DATA';
    const HTTP_FORBIDDEN = 'HTTP_FORBIDDEN';
    const HTTP_UNAUTHORIZED = 'HTTP_UNAUTHORIZED';
    const HTTP_NOT_FOUND = 'HTTP_NOT_FOUND';
    const HTTP_METHOD_NOT_ALLOWED = 'HTTP_METHOD_NOT_ALLOWED';
    const HTTP_INTERNAL_SERVER_ERROR = 'HTTP_INTERNAL_SERVER_ERROR';
    const HTTP_ALREADY_EXIST = 'HTTP_ALREADY_EXIST';
    const WRONG_CREDENTIALS = 'WRONG_CREDENTIALS';
    const USER_INACTIVE = 'USER_INACTIVE';
    const SERVER_ERROR = 'SERVER_ERROR';
    const CONFLICT = 'CONFLICT';
    const MULTI_SERVER_USER = 'MULTI_SERVER_USER';
    const USER_ALREADY_EXIST = 'USER_ALREADY_EXIST';


    static public function badRequest() {
        return static::error(400, self::BAD_REQUEST);
    }

    static public function badAttempt() {
        return static::error(400, self::BAD_ATTEMPT);
    }

    static public function forbidden() {
        return static::error(403, self::HTTP_FORBIDDEN);
    }

    static public function notFound() {
        return static::error(404, self::HTTP_NOT_FOUND);
    }

    static function error($status, $errorMessage) {
        return response()->json([
            'message' => $errorMessage,
            'success' => false
        ], $status);
    }
}
