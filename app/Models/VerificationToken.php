<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationToken extends Model
{
    protected $guarded = [];
    protected $table = 'verification_token';
}
