<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $guarded = [];

    protected $hidden = [];

    // protected $dateFormat = 'YYYY-MM-DD';

    public function user()
    {
        return $this->belongsTo('\App\Models\User');
    }
}
