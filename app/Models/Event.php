<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'start_at',
        'end_at',
        'image',
        'latitude',
        'longitude',
        'address'
    ];
    /**
     * Связь с категориями (многие ко многим)
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function ratings()
    {
        return $this->hasMany(EventRating::class);
    }

    // Акцессор для среднего рейтинга
    public function getAverageRatingAttribute()
    {
        return $this->ratings()->avg('rating') ?: 0;
    }

    /**
     * Связь с хэштегами (многие ко многим)
     */
    public function hashtags()
    {
        return $this->belongsToMany(Hashtag::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorites::class);
    }

    // Проверка, добавил ли пользователь событие в избранное
    public function isFavoritedBy(User $user)
    {
        return $this->favorites()->where('user_id', $user->id)->exists();
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    // Акцессор для среднего рейтинга событий по отзывам
    public function getAverageFeedbackRatingAttribute()
    {
        return $this->feedbacks()->avg('rating') ?: 0;
    }

    // Проверка, оставил ли пользователь отзыв
    public function hasFeedbackFrom(User $user)
    {
        return $this->feedbacks()->where('user_id', $user->id)->exists();
    }
}
