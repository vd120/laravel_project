<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|min:3|max:50|unique:users,name|regex:/^[a-zA-Z0-9_-]+$/',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'username.min' => 'Username must be at least 3 characters long.',
        ]);

        $user = User::create([
            'name' => $request->username, // Use username as the 'name' field for route key binding
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Create a basic profile for the user
        $user->profile()->create([]);

        Auth::login($user);

        return redirect('/');
    }
}
