<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class PostController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    public function index()
    {
        $users = User::where('id', '!=', auth()->id())->latest()->get();
        $currentUser = auth()->user();
        $posts = Post::with(['user', 'likes', 'comments.user'])
            ->latest()
            ->get()
            ->map(function ($post) use ($currentUser) {
                $post->isLiked = $post->isLikedBy($currentUser);
                return $post;
            });

        return Inertia::render('Posts/Index', [
            'posts' => [
                'data' => $posts
            ],
            'users' => $users
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
    }    
    
    public function like(Post $post, Request $request)
    {
        try {
            $user = $request->user();
            $result = $post->likes()->toggle($user->id);
            
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
        $currentUser = auth()->user();
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
            'isLiked' => $post->isLikedBy($currentUser)
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

    public function edit(Post $post)
    {
        $this->authorize('update', $post);
        
        return response()->json([
            'post' => $post
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);
        
        $validated = $request->validate([
            'caption' => 'required|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($post->image_path) {
                Storage::disk('public')->delete($post->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('posts', 'public');
        }

        $post->update([
            'caption' => $validated['caption'],
            'image_path' => $validated['image_path'] ?? $post->image_path
        ]);

        $post->load(['user', 'likes', 'comments.user']);
        
        return response()->json([
            'post' => $post,
            'message' => 'Post updated successfully'
        ]);
    }

    public function destroy(Post $post)
    {
        try {
            $this->authorize('delete', $post);
            
            // Begin transaction to ensure all related data is deleted properly
            \DB::beginTransaction();
            
            // Delete associated comments first
            $post->comments()->delete();
            
            // Delete associated likes
            $post->likes()->detach();
            
            // Delete the image file if it exists
            if ($post->image_path) {
                try {
                    Storage::disk('public')->delete($post->image_path);
                } catch (\Exception $e) {
                    \Log::error('Error deleting post image: ' . $e->getMessage());
                    // Continue with post deletion even if image deletion fails
                }
            }
            
            // Delete the post
            $post->delete();
            
            \DB::commit();
            
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Post deleted successfully']);
            }
            
            return redirect()->route('posts.index');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error deleting post: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Failed to delete post',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
