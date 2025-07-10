<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventRating extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'rating'
    ];

    /**
     * Связь с событием
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Связь с пользователем (если авторизация есть)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
