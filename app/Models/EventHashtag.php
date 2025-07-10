<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventHashtag extends Model
{
    protected $fillable = [
        'event_id',
        'hashtag_id'
    ];
}
