<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventReaction;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class EventService
{
    /**
     * Create a life event
     */
    public function createEvent(array $data): Event
    {
        return Event::create($data);
    }

    /**
     * Update an event
     */
    public function updateEvent(Event $event, array $data): Event
    {
        $event->update($data);
        return $event->fresh();
    }

    /**
     * Delete an event
     */
    public function deleteEvent(Event $event): bool
    {
        return $event->delete();
    }

    /**
     * Get events for a user
     */
    public function getUserEvents(User $user, ?User $viewer = null, bool $includePrivate = false)
    {
        $query = Event::where('user_id', $user->id);

        if (!$includePrivate && (!$viewer || $viewer->id !== $user->id)) {
            $query->where('is_private', false);
        }

        return $query->orderBy('event_date', 'desc')->get();
    }

    /**
     * Get memory book (all life events for a user)
     */
    public function getMemoryBook(User $user, ?User $viewer = null)
    {
        $query = Event::where('user_id', $user->id)
            ->where('is_private', false);

        if ($viewer && $viewer->id === $user->id) {
            $query = Event::where('user_id', $user->id);
        }

        return $query->orderBy('event_date', 'desc')
            ->with(['user.profile', 'reactions.user'])
            ->get()
            ->groupBy('event_type');
    }

    /**
     * Get upcoming events for friends
     */
    public function getUpcomingEventsForUser(User $user, int $days = 30)
    {
        $followingIds = $user->following()->pluck('followed_id');

        return Event::whereIn('user_id', $followingIds)
            ->where('is_private', false)
            ->whereBetween('event_date', [now(), now()->addDays($days)])
            ->with(['user.profile'])
            ->orderBy('event_date')
            ->get();
    }

    /**
     * Send birthday reminder notifications
     */
    public function sendBirthdayReminders()
    {
        $today = now();
        $tomorrow = now()->addDay();

        // Get users with birthdays today
        $birthdayUsers = User::whereHas('profile', function ($query) use ($today) {
            $query->whereRaw("DAY(birth_date) = {$today->day}")
                ->whereRaw("MONTH(birth_date) = {$today->month}");
        })->with(['profile', 'followers.followed'])->get();

        foreach ($birthdayUsers as $user) {
            // Notify followers about the birthday
            foreach ($user->followers as $follower) {
                Notification::create([
                    'user_id' => $follower->id,
                    'type' => 'birthday',
                    'data' => [
                        'birthday_user_name' => $user->name,
                        'birthday_user_username' => $user->username,
                        'birthday_user_id' => $user->id,
                        'is_today' => true,
                    ],
                ]);
            }
        }

        // Get users with birthdays tomorrow (for advance reminders)
        $tomorrowBirthdayUsers = User::whereHas('profile', function ($query) use ($tomorrow) {
            $query->whereRaw("DAY(birth_date) = {$tomorrow->day}")
                ->whereRaw("MONTH(birth_date) = {$tomorrow->month}");
        })->with(['profile', 'followers.followed'])->get();

        foreach ($tomorrowBirthdayUsers as $user) {
            foreach ($user->followers as $follower) {
                Notification::create([
                    'user_id' => $follower->id,
                    'type' => 'birthday_reminder',
                    'data' => [
                        'birthday_user_name' => $user->name,
                        'birthday_user_username' => $user->username,
                        'is_today' => false,
                    ],
                ]);
            }
        }
    }

    /**
     * Send anniversary reminders
     */
    public function sendAnniversaryReminders()
    {
        $today = now();

        // Get anniversaries happening today
        $anniversaries = Event::where('is_anniversary', true)
            ->whereRaw("DAY(event_date) = {$today->day}")
            ->whereRaw("MONTH(event_date) = {$today->month}")
            ->where('is_private', false)
            ->with(['user.followers.followed'])
            ->get();

        foreach ($anniversaries as $event) {
            $years = $event->years_since ?? 1;

            foreach ($event->user->followers as $follower) {
                Notification::create([
                    'user_id' => $follower->id,
                    'type' => 'anniversary',
                    'data' => [
                        'event_title' => $event->title,
                        'event_type' => $event->event_type,
                        'years' => $years,
                        'user_name' => $event->user->name,
                        'user_username' => $event->user->username,
                        'user_id' => $event->user_id,
                    ],
                    'related_id' => $event->id,
                    'related_type' => Event::class,
                ]);
            }
        }
    }

    /**
     * React to an event
     */
    public function reactToEvent(Event $event, User $user, string $emoji)
    {
        return DB::transaction(function () use ($event, $user, $emoji) {
            // Remove existing reaction if any
            EventReaction::where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->delete();

            // Create new reaction
            $reaction = EventReaction::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'reaction_type' => $emoji,
            ]);

            // Create notification for event owner (if not reacting to own event)
            if ($event->user_id !== $user->id) {
                Notification::create([
                    'user_id' => $event->user_id,
                    'type' => 'event_reaction',
                    'data' => [
                        'reactor_name' => $user->name,
                        'reactor_username' => $user->username,
                        'reaction_type' => $emoji,
                        'event_title' => $event->title,
                        'event_type' => $event->event_type,
                    ],
                    'related_id' => $event->id,
                    'related_type' => Event::class,
                ]);
            }

            return $reaction;
        });
    }

    /**
     * Remove reaction from event
     */
    public function removeReaction(Event $event, User $user): bool
    {
        return EventReaction::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->delete() > 0;
    }

    /**
     * Get allowed reaction emojis for an event type
     */
    public function getAllowedReactions(string $eventType): array
    {
        return Event::REACTION_EMOJIS[$eventType] ?? array_merge(...array_values(Event::REACTION_EMOJIS));
    }

    /**
     * Get events by type for a user
     */
    public function getEventsByType(User $user, string $type, ?User $viewer = null)
    {
        $query = Event::where('user_id', $user->id)
            ->where('event_type', $type);

        if (!$viewer || $viewer->id !== $user->id) {
            $query->where('is_private', false);
        }

        return $query->orderBy('event_date', 'desc')->get();
    }

    /**
     * Get life events feed for home page
     */
    public function getLifeEventsFeed(User $user, int $limit = 20)
    {
        $followingIds = $user->following()->pluck('followed_id');
        $followingIds[] = $user->id;

        return Event::whereIn('user_id', $followingIds)
            ->where('is_private', false)
            ->with(['user.profile', 'reactions.user'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
