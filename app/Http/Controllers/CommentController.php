<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class CommentController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;    public function update(Request $request, Post $post, Comment $comment)
    {
        try {
            // Verify the comment belongs to the post
            if ($comment->post_id !== $post->id) {
                return response()->json(['error' => 'Comment does not belong to this post'], 403);
            }

            // Check if user is authorized to update this comment
            if ($comment->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'content' => 'required|string|max:1000',
            ]);

            $comment->update($validated);

            $comments = $post->comments()
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'content' => $comment->content,
                        'created_at' => $comment->created_at,
                        'user' => [
                            'id' => $comment->user->id,
                            'name' => $comment->user->name,
                        ],
                    ];
                });

            return response()->json(['comments' => $comments]);
        } catch (\Exception $e) {
            Log::error('Error updating comment: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Failed to update comment',
                'message' => $e->getMessage()
            ], 500);
        }
    }    public function destroy(Post $post, Comment $comment)
    {
        try {
            // Verify the comment belongs to the post
            if ($comment->post_id !== $post->id) {
                return response()->json(['error' => 'Comment does not belong to this post'], 403);
            }

            // Check if user is authorized to delete this comment
            if ($comment->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $comment->delete();

            $comments = $post->comments()
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'content' => $comment->content,
                        'created_at' => $comment->created_at,
                        'user' => [
                            'id' => $comment->user->id,
                            'name' => $comment->user->name,
                        ],
                    ];
                });

            return response()->json(['comments' => $comments]);
        } catch (\Exception $e) {
            Log::error('Error deleting comment: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Failed to delete comment',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
