<?php
// app/Http/Controllers/EventController.php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Tag;
use App\Http\Requests\EventFilterRequest;
use App\Http\Requests\EventReactionRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Event::with(['images', 'tags'])
                     ->active()
                     ->orderBy('created_at', 'desc');

        // Добавляем пользовательские реакции только для авторизованных пользователей
        if (auth()->check()) {
            $query->with(['userReaction' => function($q) {
                $q->where('user_id', auth()->id());
            }]);
        }

        // Поиск по названию
        if ($request->filled('search')) {
            $query->byTitle($request->search);
        }

        // Фильтр по бюджету
        if ($request->filled('budget')) {
            $budgets = is_array($request->budget) ? $request->budget : [$request->budget];
            $query->byBudget($budgets);
        }

        // Фильтр по количеству людей
        if ($request->filled('people_min')) {
            $query->byPeopleCount(
                $request->people_min, 
                $request->people_max
            );
        }

        // Фильтр по настроению
        if ($request->filled('mood')) {
            $moods = is_array($request->mood) ? $request->mood : [$request->mood];
            $query->byMood($moods);
        }

        // Фильтр по погоде
        if ($request->filled('weather')) {
            $weather = is_array($request->weather) ? $request->weather : [$request->weather];
            $query->byWeather($weather);
        }

        // Фильтр по дате
        if ($request->filled('date_start')) {
            $query->byDateRange(
                $request->date_start, 
                $request->date_end
            );
        }

        $events = $query->paginate(12)->withQueryString();

        // Получаем опции для фильтров
        $filterOptions = $this->getFilterOptions();

        return Inertia::render('Events/Index', [
            'events' => $events,
            'filterOptions' => $filterOptions,
            'filters' => $request->only([
                'search', 'budget', 'mood', 'weather', 
                'people_min', 'people_max', 'date_start', 'date_end'
            ]),
        ]);
    }

    public function show(Event $event): Response
    {
        $event->load(['images', 'tags', 'likes', 'dislikes']);
        
        if (auth()->check()) {
            $event->load(['userReaction' => function($q) {
                $q->where('user_id', auth()->id());
            }]);
        }

        $stats = [
            'likes_count' => $event->likes->count(),
            'dislikes_count' => $event->dislikes->count(),
            'user_reaction' => $event->userReaction ? $event->userReaction->reaction : null,
        ];

        return Inertia::render('Events/Show', [
            'event' => $event,
            'stats' => $stats,
        ]);
    }

    public function react(EventReactionRequest $request, Event $event)
    {
        $userId = auth()->id();
        
        // Удаляем существующую реакцию пользователя
        $event->reactions()->where('user_id', $userId)->delete();

        // Создаем новую реакцию
        $event->reactions()->create([
            'user_id' => $userId,
            'reaction' => $request->reaction
        ]);

        return back()->with('success', 'Реакция сохранена');
    }

    public function removeReaction(Event $event)
    {
        $userId = auth()->id();
        
        $deleted = $event->reactions()->where('user_id', $userId)->delete();

        if (!$deleted) {
            return back()->with('error', 'Реакция не найдена');
        }

        return back()->with('success', 'Реакция удалена');
    }

    private function getFilterOptions(): array
    {
        return [
            'budget_options' => [
                ['value' => 'free', 'label' => 'Бесплатно'],
                ['value' => 'cheap', 'label' => 'Дешево'],
                ['value' => 'medium', 'label' => 'Средне'],
                ['value' => 'expensive', 'label' => 'Дорого'],
            ],
            'mood_options' => [
                ['value' => 'fun', 'label' => 'Весело'],
                ['value' => 'romantic', 'label' => 'Романтично'],
                ['value' => 'calm', 'label' => 'Спокойно'],
                ['value' => 'active', 'label' => 'Активно'],
                ['value' => 'educational', 'label' => 'Познавательно'],
            ],
            'weather_options' => [
                ['value' => 'sunny', 'label' => 'Солнечно'],
                ['value' => 'rainy', 'label' => 'Дождливо'],
                ['value' => 'snowy', 'label' => 'Снежно'],
                ['value' => 'cloudy', 'label' => 'Облачно'],
                ['value' => 'any', 'label' => 'Любая погода'],
            ]
        ];
    }
}

// app/Http/Controllers/UserReactionController.php

namespace App\Http\Controllers;

use App\Models\UserEventReaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserReactionController extends Controller
{
    public function liked(Request $request): Response
    {
        $userId = auth()->id();
        
        $likedEvents = UserEventReaction::with(['event.images', 'event.tags'])
                                       ->where('user_id', $userId)
                                       ->where('reaction', 'like')
                                       ->paginate(12);

        return Inertia::render('User/LikedEvents', [
            'events' => $likedEvents,
        ]);
    }

    public function disliked(Request $request): Response
    {
        $userId = auth()->id();
        
        $dislikedEvents = UserEventReaction::with(['event.images', 'event.tags'])
                                          ->where('user_id', $userId)
                                          ->where('reaction', 'dislike')
                                          ->paginate(12);

        return Inertia::render('User/DislikedEvents', [
            'events' => $dislikedEvents,
        ]);
    }
}