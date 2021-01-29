<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventsGroup extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'events_group';
}
