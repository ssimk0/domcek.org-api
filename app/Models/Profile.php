<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'birth_date'
    ];

    // protected $dateFormat = 'YYYY-MM-DD';

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
