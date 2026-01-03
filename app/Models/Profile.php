<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'avatar',
        'cover_image',
        'bio',
        'website',
        'location',
        'birth_date',
        'occupation',
        'about',
        'phone',
        'gender',
        'is_private',
        'social_links',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
