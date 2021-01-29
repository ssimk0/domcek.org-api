<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $guarded = [];
    protected $table = 'events';

    public function volunteerTypes()
    {
        return $this->belongsToMany('App\Models\VolunteerType', 'event_volunteer_types', 'event_id', 'volunteer_type_id');
    }
}
