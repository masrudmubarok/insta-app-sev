<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $posts = Post::where('user_id', $user->id)
            ->withCount(['likes', 'comments'])
            ->with(['likes' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->latest()
            ->get()
            ->map(function ($post) {
                $post->is_liked = $post->likes->isNotEmpty();
                unset($post->likes);
                return $post;
            });

        return Inertia::render('Profile/Show', [
            'posts' => [
                'data' => $posts
            ]
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . Auth::id()],
        ]);
        $user = User::findOrFail(Auth::id());
        $user->update($validated);

        return back()->with('message', 'Profile updated successfully');
    }
}
