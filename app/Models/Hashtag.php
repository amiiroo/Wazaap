<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hashtag extends Model
{
    protected $fillable = [
        'name',
        'category_id'
    ];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
