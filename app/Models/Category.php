<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];


    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function hashtags()
    {
        return $this->hasMany(Hashtag::class);
    }
}
