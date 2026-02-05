<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Composite primary key - no auto-incrementing id
    public $incrementing = false;
    protected $primaryKey = ['user_id', 'event_id'];

    // Override getKey for composite keys
    public function getKey()
    {
        return [$this->user_id, $this->event_id];
    }

    // Override setKeysForSaveQuery for composite keys
    protected function setKeysForSaveQuery($query)
    {
        return $query->where('user_id', $this->user_id)
                     ->where('event_id', $this->event_id);
    }
}
