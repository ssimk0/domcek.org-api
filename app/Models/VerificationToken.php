<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationToken extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'verification_token';

    // Table has no primary key
    public $incrementing = false;
    protected $primaryKey = null;
}
