<?php

namespace App;

use Codesleeve\Stapler\ORM\EloquentTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use Illuminate\Database\Eloquent\Model;

class SliderImage extends Model implements StaplerableInterface
{
    use EloquentTrait;

    public function __construct(array $attributes = array()) {
        $this->hasAttachedFile('image');

        parent::__construct($attributes);
    }

    protected $fillable = [
        'image', 'title', 'text', 'order'
    ];
}
