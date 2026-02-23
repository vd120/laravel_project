<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Group extends Model
{
    protected $fillable = ['name', 'description', 'creator_id', 'avatar', 'is_private', 'slug', 'invite_link'];

    protected $casts = [
        'is_private' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($group) {
            if (empty($group->slug)) {
                $group->slug = Str::random(20);
            }
            if (empty($group->invite_link)) {
                $group->invite_link = Str::random(24);
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function members()
    {
        return $this->hasMany(GroupMember::class);
    }

    public function conversation()
    {
        return $this->hasOne(Conversation::class);
    }

    public function admins()
    {
        return $this->members()->where('role', 'admin');
    }

    public function regularMembers()
    {
        return $this->members()->where('role', 'member');
    }

    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    public function isAdmin(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->where('role', 'admin')->exists();
    }

    public function addMember(User $user, string $role = 'member'): GroupMember
    {
        return GroupMember::create([
            'group_id' => $this->id,
            'user_id' => $user->id,
            'role' => $role,
        ]);
    }

    public function removeMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->delete() > 0;
    }

    public static function createGroup(array $data, User $creator): self
    {
        $group = static::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'creator_id' => $creator->id,
            'avatar' => $data['avatar'] ?? null,
            'is_private' => $data['is_private'] ?? false,
        ]);

        // Add creator as admin
        $group->addMember($creator, 'admin');

        // Create conversation for the group
        Conversation::create([
            'is_group' => true,
            'group_id' => $group->id,
            'name' => $group->name,
            'avatar' => $group->avatar,
            'slug' => Str::random(24),
        ]);

        return $group;
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        
        // Return default group avatar
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=25d366&color=fff&size=200';
    }
}