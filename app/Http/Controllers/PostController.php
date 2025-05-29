<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        
        $posts = Post::with(['user', 'likes', 'comments.user'])
            ->latest()
            ->get()
            ->map(function ($post) use ($userId) {
                return [
                    'id' => $post->id,
                    'caption' => $post->caption,
                    'image_path' => $post->image_path,
                    'created_at' => $post->created_at,
                    'user' => $post->user,
                    'likes' => $post->likes->map(function($user) {
                        return ['user_id' => $user->id];
                    }),
                    'comments' => $post->comments,
                    'isLiked' => $post->likes->contains('id', $userId)
                ];
            });

        return Inertia::render('Posts/Index', [
            'posts' => [
                'data' => $posts
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'caption' => 'required|string|max:1000',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $imagePath = $request->file('image')->store('posts', 'public');

        $post = $request->user()->posts()->create([
            'caption' => $validated['caption'],
            'image_path' => $imagePath
        ]);

        $post->load(['user', 'likes', 'comments']);

        // Format the post data consistently with the index method
        $postData = [
            'id' => $post->id,
            'caption' => $post->caption,
            'image_path' => $post->image_path,
            'created_at' => $post->created_at,
            'user' => $post->user,
            'likes' => [],
            'comments' => [],
            'isLiked' => false
        ];

        if ($request->wantsJson()) {
            return response()->json($postData);
        }

        return redirect()->route('posts.index');
    }    public function like(Post $post, Request $request)
    {
        try {
            $user = $request->user();
            $result = $post->likes()->toggle($user->id);
            
            // Reload the post to get fresh relationships
            $post->load(['likes']);
            
            return response()->json([
                'likes' => $post->likes->map(function($user) {
                    return ['user_id' => $user->id];
                }),
                'isLiked' => $post->isLikedBy($user),
                'likesCount' => $post->likes()->count()
            ]);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'message' => 'Error toggling like',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Post $post, Request $request)
    {
        $userId = auth()->id();
        $post->load(['user', 'likes', 'comments.user']);
        
        $postData = [
            'id' => $post->id,
            'caption' => $post->caption,
            'image_path' => $post->image_path,
            'created_at' => $post->created_at,
            'user' => $post->user,
            'likes' => $post->likes->map(function($user) {
                return ['user_id' => $user->id];
            }),
            'comments' => $post->comments,
            'isLiked' => $post->likes->contains('id', $userId)
        ];
        
        if ($request->wantsJson()) {
            return response()->json($postData);
        }
        
        return Inertia::render('Posts/Show', [
            'post' => $postData
        ]);
    }

    public function comment(Post $post, Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $validated['content']
        ]);

        $post->load(['comments.user']);

        return response()->json([
            'comments' => $post->comments
        ]);
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);
        
        if ($post->image_path) {
            Storage::disk('public')->delete($post->image_path);
        }
        
        $post->delete();
        
        return redirect()->route('posts.index');
    }
}
