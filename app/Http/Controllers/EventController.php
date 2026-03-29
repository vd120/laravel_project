<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use App\Services\EventService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    protected EventService $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    /**
     * Display life events feed
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $events = $this->eventService->getLifeEventsFeed($user, 20);

        return view('events.index', compact('events'));
    }

    /**
     * Display memory book for a user
     */
    public function memoryBook(User $user)
    {
        $viewer = Auth::user();
        
        // Only allow viewing own memory book or if public
        if ($user->id !== $viewer?->id) {
            return redirect()->route('users.show', $user)
                ->with('error', 'You can only view your own memory book.');
        }

        $memoryBook = $this->eventService->getMemoryBook($user, $viewer);
        $eventTypes = Event::EVENT_TYPES;
        $eventIcons = Event::EVENT_ICONS;

        return view('events.memory-book', compact('memoryBook', 'eventTypes', 'eventIcons'));
    }

    /**
     * Show the form for creating a new event
     */
    public function create()
    {
        $eventTypes = Event::EVENT_TYPES;
        $eventIcons = Event::EVENT_ICONS;

        return view('events.create', compact('eventTypes', 'eventIcons'));
    }

    /**
     * Store a newly created event
     */
    public function store(Request $request)
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

        $validated['user_id'] = Auth::id();
        $validated['is_anniversary'] = $request->boolean('is_anniversary', false);
        $validated['is_private'] = $request->boolean('is_private', false);

        $event = $this->eventService->createEvent($validated);

        // Return JSON for AJAX requests (modal)
        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Life event created successfully! 🎉',
                'data' => [
                    'id' => $event->id,
                    'type' => $event->event_type,
                    'title' => $event->title,
                    'url' => route('events.show', $event->id),
                ],
            ]);
        }

        // Redirect for regular form submissions
        return redirect()->route('events.memory-book', Auth::user())
            ->with('success', 'Life event created successfully!');
    }

    /**
     * Display the specified event
     */
    public function show(Event $event)
    {
        $viewer = Auth::user();

        // Check privacy
        if ($event->is_private && $event->user_id !== $viewer?->id) {
            abort(403, 'This event is private.');
        }

        $allowedReactions = $this->eventService->getAllowedReactions($event->event_type);
        $groupedReactions = $event->getGroupedReactions();

        return view('events.show', compact('event', 'allowedReactions', 'groupedReactions'));
    }

    /**
     * Show the form for editing the specified event
     */
    public function edit(Event $event)
    {
        // Only owner can edit
        if ($event->user_id !== Auth::id()) {
            abort(403, 'You can only edit your own events.');
        }

        $eventTypes = Event::EVENT_TYPES;
        $eventIcons = Event::EVENT_ICONS;

        return view('events.edit', compact('event', 'eventTypes', 'eventIcons'));
    }

    /**
     * Update the specified event
     */
    public function update(Request $request, Event $event)
    {
        // Only owner can update
        if ($event->user_id !== Auth::id()) {
            abort(403, 'You can only update your own events.');
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

        return redirect()->route('events.show', $event)
            ->with('success', 'Event updated successfully!');
    }

    /**
     * Remove the specified event
     */
    public function destroy(Event $event)
    {
        // Only owner can delete
        if ($event->user_id !== Auth::id()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'You can only delete your own events.'], 403);
            }
            abort(403, 'You can only delete your own events.');
        }

        $this->eventService->deleteEvent($event);

        // Return JSON for AJAX requests
        if (request()->expectsJson()) {
            return response()->json(['message' => 'Event deleted successfully!']);
        }

        return redirect()->route('events.memory-book', Auth::user())
            ->with('success', 'Event deleted successfully!');
    }

    /**
     * React to an event
     */
    public function react(Request $request, Event $event)
    {
        $validated = $request->validate([
            'emoji' => 'required|string|max:10',
        ]);

        $allowedReactions = $this->eventService->getAllowedReactions($event->event_type);
        
        if (!in_array($validated['emoji'], $allowedReactions)) {
            return back()->with('error', 'Invalid reaction for this event type.');
        }

        $this->eventService->reactToEvent($event, Auth::user(), $validated['emoji']);

        return back()->with('success', 'Reaction added!');
    }

    /**
     * Remove reaction from an event
     */
    public function removeReaction(Event $event)
    {
        $this->eventService->removeReaction($event, Auth::user());

        return back()->with('success', 'Reaction removed!');
    }

    /**
     * Get upcoming events for the current user
     */
    public function upcoming()
    {
        $user = Auth::user();
        $events = $this->eventService->getUpcomingEventsForUser($user, 30);

        return view('events.upcoming', compact('events'));
    }
}
