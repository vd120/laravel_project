<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    protected EventService $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    /**
     * Get life events feed
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $events = $this->eventService->getLifeEventsFeed($user, 20);

        return response()->json([
            'success' => true,
            'data' => $events->map(fn($event) => [
                'id' => $event->id,
                'type' => $event->event_type,
                'title' => $event->title,
                'description' => $event->description,
                'event_date' => $event->event_date->toDateString(),
                'formatted_date' => $event->formatted_date,
                'icon' => $event->icon,
                'is_anniversary' => $event->is_anniversary,
                'years_since' => $event->years_since,
                'user' => [
                    'id' => $event->user->id,
                    'name' => $event->user->name,
                    'username' => $event->user->username,
                    'avatar' => $event->user->profile?->avatar,
                ],
                'reactions_count' => $event->reactions->count(),
                'created_at' => $event->created_at->toIso8601String(),
            ]),
        ]);
    }

    /**
     * Get user's events
     */
    public function userEvents(Request $request, int $userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        $viewer = auth()->user();
        
        $events = $this->eventService->getUserEvents($user, $viewer);

        return response()->json([
            'success' => true,
            'data' => $events->map(fn($event) => $this->formatEvent($event)),
        ]);
    }

    /**
     * Get memory book for a user
     */
    public function memoryBook(Request $request, int $userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        $viewer = auth()->user();
        
        $memoryBook = $this->eventService->getMemoryBook($user, $viewer);

        $formatted = [];
        foreach ($memoryBook as $type => $events) {
            $formatted[$type] = $events->map(fn($event) => $this->formatEvent($event));
        }

        return response()->json([
            'success' => true,
            'data' => $formatted,
        ]);
    }

    /**
     * Store a new event
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_type' => 'required|string|in:' . implode(',', array_keys(Event::EVENT_TYPES)),
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'event_date' => 'required|date',
            'year' => 'nullable|integer|min:1900|max:' . now()->year,
            'is_anniversary' => 'boolean',
            'is_private' => 'boolean',
            'badge_icon' => 'nullable|string|max:10',
            'metadata' => 'nullable|array',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['is_anniversary'] = $request->boolean('is_anniversary', false);
        $validated['is_private'] = $request->boolean('is_private', false);

        $event = $this->eventService->createEvent($validated);

        return response()->json([
            'success' => true,
            'message' => 'Event created successfully!',
            'data' => $this->formatEvent($event),
        ], 201);
    }

    /**
     * Update an event
     */
    public function update(Request $request, string $eventSlug): JsonResponse
    {
        $event = Event::where('slug', $eventSlug)->firstOrFail();

        if ($event->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'event_type' => 'required|string|in:' . implode(',', array_keys(Event::EVENT_TYPES)),
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'event_date' => 'required|date',
            'year' => 'nullable|integer|min:1900|max:' . now()->year,
            'is_anniversary' => 'boolean',
            'is_private' => 'boolean',
            'badge_icon' => 'nullable|string|max:10',
            'metadata' => 'nullable|array',
        ]);

        $validated['is_anniversary'] = $request->boolean('is_anniversary', false);
        $validated['is_private'] = $request->boolean('is_private', false);

        $event = $this->eventService->updateEvent($event, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Event updated successfully!',
            'data' => $this->formatEvent($event),
        ]);
    }

    /**
     * Delete an event
     */
    public function destroy(string $eventSlug): JsonResponse
    {
        $event = Event::where('slug', $eventSlug)->firstOrFail();

        if ($event->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $this->eventService->deleteEvent($event);

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully!',
        ]);
    }

    /**
     * React to an event
     */
    public function react(string $eventSlug, Request $request): JsonResponse
    {
        $event = Event::where('slug', $eventSlug)->firstOrFail();

        $validated = $request->validate([
            'emoji' => 'required|string|max:10',
        ]);

        $allowedReactions = $this->eventService->getAllowedReactions($event->event_type);
        
        if (!in_array($validated['emoji'], $allowedReactions)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid reaction for this event type.',
            ], 422);
        }

        $reaction = $this->eventService->reactToEvent($event, auth()->user(), $validated['emoji']);

        return response()->json([
            'success' => true,
            'message' => 'Reaction added!',
            'data' => [
                'reaction_type' => $reaction->reaction_type,
            ],
        ]);
    }

    /**
     * Remove reaction from an event
     */
    public function removeReaction(string $eventSlug): JsonResponse
    {
        $event = Event::where('slug', $eventSlug)->firstOrFail();

        $this->eventService->removeReaction($event, auth()->user());

        return response()->json([
            'success' => true,
            'message' => 'Reaction removed!',
        ]);
    }

    /**
     * Get upcoming events
     */
    public function upcoming(Request $request): JsonResponse
    {
        $user = auth()->user();
        $events = $this->eventService->getUpcomingEventsForUser($user, 30);

        return response()->json([
            'success' => true,
            'data' => $events->map(fn($event) => [
                'id' => $event->id,
                'type' => $event->event_type,
                'title' => $event->title,
                'event_date' => $event->event_date->toDateString(),
                'formatted_date' => $event->formatted_date,
                'icon' => $event->icon,
                'is_anniversary' => $event->is_anniversary,
                'user' => [
                    'id' => $event->user->id,
                    'name' => $event->user->name,
                    'username' => $event->user->username,
                    'avatar' => $event->user->profile?->avatar,
                ],
            ]),
        ]);
    }

    /**
     * Format event for API response
     */
    private function formatEvent(Event $event): array
    {
        return [
            'id' => $event->id,
            'slug' => $event->slug,
            'type' => $event->event_type,
            'title' => $event->title,
            'description' => $event->description,
            'event_date' => $event->event_date->toDateString(),
            'formatted_date' => $event->formatted_date,
            'icon' => $event->icon,
            'is_anniversary' => $event->is_anniversary,
            'years_since' => $event->years_since,
            'is_private' => $event->is_private,
            'user' => [
                'id' => $event->user->id,
                'name' => $event->user->name,
                'username' => $event->user->username,
                'avatar' => $event->user->profile?->avatar,
            ],
            'reactions' => $event->reactions->map(fn($reaction) => [
                'user_id' => $reaction->user_id,
                'emoji' => $reaction->reaction_type,
            ]),
            'created_at' => $event->created_at->toIso8601String(),
            'updated_at' => $event->updated_at->toIso8601String(),
        ];
    }
}
