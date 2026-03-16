<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GroupController extends Controller
{
    /**
     * Show form to create a new group
     */
    public function create()
    {
        $friends = auth()->user()->following()->get();
        
        return view('groups.create', compact('friends'));
    }

    /**
     * Store a new group
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('groups/avatars', 'public');
        }

        // Create group with slug and invite link
        $group = Group::create([
            'name' => $request->name,
            'description' => $request->description,
            'creator_id' => auth()->id(),
            'avatar' => $avatarPath,
            'is_private' => $request->boolean('is_private'),
            'slug' => Str::random(20),
            'invite_link' => Str::random(24),
        ]);

        // Add creator as admin
        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => auth()->id(),
            'role' => 'admin',
        ]);

        // Add selected members (if any)
        if ($request->members && is_array($request->members)) {
            foreach ($request->members as $memberId) {
                if ($memberId != auth()->id()) {
                    GroupMember::create([
                        'group_id' => $group->id,
                        'user_id' => $memberId,
                        'role' => 'member',
                    ]);
                }
            }
        }

        // Create conversation for the group
        $conversation = Conversation::create([
            'user1_id' => auth()->id(),
            'is_group' => true,
            'group_id' => $group->id,
            'name' => $group->name,
            'avatar' => $group->avatar,
            'slug' => Str::random(24),
        ]);

        // Create system message for group creation
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'content' => auth()->user()->username . ' created this group',
            'type' => 'system',
        ]);

        return redirect()->route('chat.show', $conversation)
            ->with('success', __('messages.group_created'));
    }

    /**
     * Show group details
     */
    public function show(Request $request, $slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        
        if (!$group->hasMember(auth()->user())) {
            abort(403, 'You are not a member of this group.');
        }

        $group->load(['members.user', 'creator']);
        
        return view('groups.show', compact('group'));
    }

    /**
     * Show form to edit group
     */
    public function edit($slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        
        if (!$group->isAdmin(auth()->user())) {
            abort(403, 'Only admins can edit the group.');
        }

        $friends = auth()->user()->following()->get();
        
        return view('groups.edit', compact('group', 'friends'));
    }

    /**
     * Update group details
     */
    public function update(Request $request, $slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        
        if (!$group->isAdmin(auth()->user())) {
            abort(403, 'Only admins can edit the group.');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ]);

        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'is_private' => $request->boolean('is_private'),
        ];

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('groups/avatars', 'public');
        }

        $group->update($data);

        // Update conversation name and avatar
        if ($group->conversation) {
            $group->conversation->update([
                'name' => $group->name,
                'avatar' => $group->avatar,
            ]);
        }

        return redirect()->route('groups.show', $group->slug)
            ->with('success', __('messages.group_updated'));
    }

    /**
     * Add members to group
     */
    public function addMembers(Request $request, $slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        
        if (!$group->isAdmin(auth()->user())) {
            abort(403, 'Only admins can add members.');
        }

        $request->validate([
            'members' => 'required|array|min:1',
            'members.*' => 'exists:users,id',
        ]);

        $added = 0;
        foreach ($request->members as $memberId) {
            if (!$group->hasMember(User::find($memberId))) {
                GroupMember::create([
                    'group_id' => $group->id,
                    'user_id' => $memberId,
                    'role' => 'member',
                ]);
                $added++;
                
                // Create system message for member addition
                $addedUser = User::find($memberId);
                Message::create([
                    'conversation_id' => $group->conversation->id,
                    'sender_id' => $memberId,
                    'content' => $addedUser->username . ' added to the group by ' . auth()->user()->username,
                    'type' => 'system',
                ]);
            }
        }

        return redirect()->route('groups.show', $group->slug)
            ->with('success', __('messages.members_added', ['count' => $added]));
    }

    /**
     * Remove member from group
     */
    public function removeMember($slug, $userId)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        $user = User::findOrFail($userId);

        if (!$group->isAdmin(auth()->user()) && auth()->id() !== $user->id) {
            abort(403, 'Only admins can remove members.');
        }

        // Can't remove the last admin
        if ($group->isAdmin($user) && $group->admins()->count() === 1) {
            return redirect()->route('groups.show', $group->slug)
                ->with('error', __('messages.cannot_remove_last_admin'));
        }

        // Create system message before removing
        if (auth()->id() === $user->id) {
            // User is leaving voluntarily
            Message::create([
                'conversation_id' => $group->conversation->id,
                'sender_id' => $user->id,
                'content' => $user->username . ' exited group',
                'type' => 'system',
            ]);
            $group->conversation->update(['last_message_at' => now()]);
        } else {
            // Admin is removing user
            $admin = auth()->user();

            // Public message (everyone sees)
            Message::create([
                'conversation_id' => $group->conversation->id,
                'sender_id' => $admin->id,
                'content' => $admin->username . ' removed ' . $user->username,
                'type' => 'system',
            ]);

            // Private message to removed user only
            Message::create([
                'conversation_id' => $group->conversation->id,
                'sender_id' => $admin->id,
                'visible_to' => $user->id,  // Only visible to removed user
                'content' => 'You were removed from group',
                'type' => 'system',
            ]);
            
            // Update conversation timestamp
            $group->conversation->update(['last_message_at' => now()]);
        }

        $group->removeMember($user);

        // If member removed themselves
        if (auth()->id() === $user->id) {
            return redirect()->route('chat.index')
                ->with('success', __('messages.left_group'));
        }

        return redirect()->route('groups.show', $group->slug)
            ->with('success', __('messages.member_removed'));
    }

    /**
     * Make member an admin
     */
    public function makeAdmin($slug, $userId)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        $user = User::findOrFail($userId);

        if (!$group->isAdmin(auth()->user())) {
            abort(403, 'Only admins can promote members.');
        }

        $member = $group->members()->where('user_id', $user->id)->first();

        if ($member) {
            $member->update(['role' => 'admin']);
        }

        // Create system message in group chat (visible to all)
        $admin = auth()->user();
        Message::create([
            'conversation_id' => $group->conversation->id,
            'sender_id' => $admin->id,
            'content' => $admin->username . ' made ' . $user->username . ' an admin',
            'type' => 'system',
        ]);

        // Send "You are admin now" message visible ONLY to promoted user
        Message::create([
            'conversation_id' => $group->conversation->id,
            'sender_id' => $admin->id,
            'visible_to' => $user->id,  // Only visible to this user
            'content' => 'You are admin now',
            'type' => 'system',
        ]);
        
        // Update conversation timestamp
        $group->conversation->update(['last_message_at' => now()]);

        return redirect()->route('groups.show', $group->slug)
            ->with('success', __('messages.admin_promoted', ['username' => $user->username]));
    }

    /**
     * Remove admin role from member (demote to regular member)
     */
    public function removeAdmin($slug, $userId)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        $user = User::findOrFail($userId);
        
        if (!$group->isAdmin(auth()->user())) {
            abort(403, 'Only admins can remove admin privileges.');
        }

        // Can't remove admin from yourself
        if (auth()->id() === $user->id) {
            return redirect()->route('groups.show', $group->slug)
                ->with('error', __('messages.cannot_remove_own_admin'));
        }

        // Can't remove the last admin
        if ($group->admins()->count() === 1) {
            return redirect()->route('groups.show', $group->slug)
                ->with('error', __('messages.cannot_remove_last_admin'));
        }

        $member = $group->members()->where('user_id', $user->id)->first();

        if ($member && $member->role === 'admin') {
            $member->update(['role' => 'member']);
        }

        // Create system message in group chat
        $admin = auth()->user();
        Message::create([
            'conversation_id' => $group->conversation->id,
            'sender_id' => $admin->id,
            'content' => $admin->username . ' removed ' . $user->username . ' from admin',
            'type' => 'system',
        ]);
        
        // Update conversation timestamp
        $group->conversation->update(['last_message_at' => now()]);

        return redirect()->route('groups.show', $group->slug)
            ->with('success', __('messages.admin_demoted', ['username' => $user->username]));
    }

    /**
     * Delete group
     */
    public function destroy($slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        
        if (!$group->isAdmin(auth()->user())) {
            abort(403, 'Only admins can delete the group.');
        }

        $group->conversation?->delete();
        $group->members()->delete();
        $group->delete();

        return redirect()->route('chat.index')
            ->with('success', __('messages.group_deleted'));
    }

    /**
     * Get user's groups
     */
    public function index()
    {
        $groups = Group::whereHas('members', function ($query) {
            $query->where('user_id', auth()->id());
        })->with(['members.user', 'conversation'])->latest()->get();

        return view('groups.index', compact('groups'));
    }

    /**
     * Join group via invite link
     */
    public function joinViaInvite($inviteLink)
    {
        $group = Group::where('invite_link', $inviteLink)->firstOrFail();

        // Check if user is already a member
        if ($group->hasMember(auth()->user())) {
            return redirect()->route('chat.show', $group->conversation)
                ->with('info', __('messages.already_in_group'));
        }

        // Add user to group
        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => auth()->id(),
            'role' => 'member',
        ]);

        // Create system message for joining via invite link
        Message::create([
            'conversation_id' => $group->conversation->id,
            'sender_id' => auth()->id(),
            'content' => auth()->user()->username . ' joined the group using invite link',
            'type' => 'system',
        ]);

        // Ensure the group conversation reflects latest activity in previews
        $group->conversation->update(['last_message_at' => now()]);

        return redirect()->route('chat.show', $group->conversation)
            ->with('success', __('messages.joined_group'));
    }

    /**
     * Regenerate invite link
     */
    public function regenerateInvite($slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        
        if (!$group->isAdmin(auth()->user())) {
            abort(403, 'Only admins can regenerate invite links.');
        }

        $group->update([
            'invite_link' => Str::random(24),
        ]);

        return redirect()->route('groups.show', $group->slug)
            ->with('success', __('messages.invite_link_regenerated'));
    }

    /**
     * Send quick invite to selected users via private message
     */
    public function quickInvite(Request $request, $slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        
        if (!$group->isAdmin(auth()->user())) {
            abort(403, 'Only admins can send invites.');
        }

        $request->validate([
            'users' => 'required|array|min:1',
            'users.*' => 'exists:users,id',
        ]);

        $invited = 0;
        $alreadyMembers = 0;
        
        foreach ($request->users as $userId) {
            $user = User::find($userId);
            
            // Skip if already a member
            if ($group->hasMember($user)) {
                $alreadyMembers++;
                continue;
            }
            
            // Find or create a private conversation with this user
            $conversation = Conversation::where(function ($q) use ($userId) {
                $q->where('user1_id', auth()->id())
                  ->where('user2_id', $userId);
            })->orWhere(function ($q) use ($userId) {
                $q->where('user1_id', $userId)
                  ->where('user2_id', auth()->id());
            })->where('is_group', false)->first();
            
            if (!$conversation) {
                $conversation = Conversation::create([
                    'user1_id' => auth()->id(),
                    'user2_id' => $userId,
                    'is_group' => false,
                ]);
            }
            
            // Send a group invite message
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => auth()->id(),
                'content' => '',  // Empty string - the invite card shows the info
                'type' => 'group_invite',
                'media_path' => json_encode([
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'group_slug' => $group->slug,
                    'invite_link' => $group->invite_link,
                ]),
            ]);

            // Update conversation timestamp
            $conversation->update(['last_message_at' => now()]);
            
            // Also create a notification
            $inviter = auth()->user();
            \App\Models\Notification::create([
                'user_id' => $userId,
                'type' => 'group_invite',
                'data' => [
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'group_slug' => $group->slug,
                    'inviter_id' => $inviter->id,
                    'inviter_username' => $inviter->username ?? 'Someone',
                    'invite_link' => $group->invite_link,
                    'conversation_id' => $conversation->id,
                ],
                'read_at' => null,
            ]);
            
            $invited++;
        }

        $message = $invited . ' invite(s) sent successfully!';
        if ($alreadyMembers > 0) {
            $message .= ' (' . $alreadyMembers . ' user(s) already members)';
        }

        return redirect()->route('groups.show', $group->slug)
            ->with('success', $message);
    }

    /**
     * Accept group invite from private message
     */
    public function acceptInvite($inviteLink)
    {
        $group = Group::where('invite_link', $inviteLink)->firstOrFail();

        // Check if user is already a member
        if ($group->hasMember(auth()->user())) {
            return response()->json([
                'success' => false,
                'message' => __('messages.already_in_group')
            ]);
        }

        // Find the invite message to get who sent the invite
        $inviteMessage = Message::where('type', 'group_invite')
            ->whereHas('conversation', function($query) {
                $query->where('is_group', false)
                    ->where(function($q) {
                        $q->where('user1_id', auth()->id())
                          ->orWhere('user2_id', auth()->id());
                    });
            })
            ->whereRaw("JSON_EXTRACT(media_path, '$.invite_link') = ?", [$inviteLink])
            ->latest()
            ->first();

        $inviter = null;
        if ($inviteMessage && $inviteMessage->sender) {
            $inviter = $inviteMessage->sender;
        }

        // Add user to group
        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => auth()->id(),
            'role' => 'member',
        ]);

        // Create system message in group chat
        if ($inviter) {
            // Quick invite - show "inviter added new_member"
            Message::create([
                'conversation_id' => $group->conversation->id,
                'sender_id' => $inviter->id,
                'content' => $inviter->username . ' added ' . auth()->user()->username,
                'type' => 'system',
            ]);
        } else {
            // Direct invite link - show "username joined using group invite link"
            Message::create([
                'conversation_id' => $group->conversation->id,
                'sender_id' => auth()->id(),
                'content' => auth()->user()->username . ' joined using group invite link',
                'type' => 'system',
            ]);
        }
        
        // Update conversation timestamp
        $group->conversation->update(['last_message_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => __('messages.joined_group'),
            'redirect' => route('chat.show', $group->conversation)
        ]);
    }
}
