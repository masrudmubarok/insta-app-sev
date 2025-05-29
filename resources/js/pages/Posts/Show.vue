<script setup lang="ts">
import { Head, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

const page = usePage();
const user = ref(page.props.auth.user);

interface Like {
  user_id: number;
}

interface Comment {
  id: number;
  content: string;
  user: {
    id: number;
    name: string;
  };
  created_at: string;
}

interface Post {
  id: number;
  caption: string;
  image_path: string;
  created_at: string;
  user: {
    id: number;
    name: string;
  };
  likes: Like[];
  isLiked: boolean;
  comments: Comment[];
}

const props = defineProps<{
  post: Post;
}>();

const post = ref<Post>(props.post);
const commentForm = ref('');

const isLiked = computed(() => post.value.isLiked);

const getLikeButtonClasses = computed(() => {
  return [
    'flex items-center space-x-2 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95',
    isLiked.value ? 'text-red-500 hover:text-red-600' : 'text-gray-500 hover:text-gray-600'
  ];
});

const closeModal = () => {
  router.visit('/posts', {
    preserveState: true,
    preserveScroll: true,
    replace: true
  });
};

const toggleLike = async () => {
  // Store current state for rollback
  const currentState = {
    likes: [...post.value.likes],
    isLiked: post.value.isLiked
  };

  // Optimistic update
  post.value.isLiked = !post.value.isLiked;
  if (post.value.isLiked) {
    post.value.likes.push({ user_id: user.value?.id });
  } else {
    post.value.likes = post.value.likes.filter(like => like.user_id !== user.value?.id);
  }

  try {
    const response = await axios.post(`/posts/${post.value.id}/like`, null, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });
    
    // Update with server response to ensure consistency
    if (response.data) {
      post.value.likes = response.data.likes;
      post.value.isLiked = response.data.isLiked;
    }
  } catch (error) {
    // Revert to previous state if error occurs
    console.error('Error toggling like:', error);
    post.value.likes = currentState.likes;
    post.value.isLiked = currentState.isLiked;
  }
};

const submitComment = async () => {
  if (!commentForm.value.trim()) return;

  try {
    const response = await axios.post(`/posts/${post.value.id}/comment`, {
      content: commentForm.value,
    });
    
    if (response.data.comments) {
      post.value.comments = response.data.comments;
    }
    commentForm.value = '';
  } catch (error) {
    console.error('Error posting comment:', error);
  }
};
</script>

<template>
  <div class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeModal">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 transition-opacity bg-black bg-opacity-75"></div>

      <!-- Modal panel -->
      <div class="inline-block w-full max-w-6xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl sm:rounded-lg">
        <div class="absolute top-0 right-0 pt-4 pr-4">
          <button
            type="button"
            @click="closeModal"
            class="text-gray-400 bg-white rounded-full p-1 hover:text-gray-500 focus:outline-none"
          >
            <span class="sr-only">Close</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="flex h-[80vh]">
          <!-- Left side - Image -->
          <div class="flex-1 bg-black flex items-center">
            <img
              :src="`/storage/${post.image_path}`"
              :alt="post.caption"
              class="w-full h-full object-contain"
            />
          </div>

          <!-- Right side - Comments and Interactions -->
          <div class="w-[400px] flex flex-col bg-white">
            <!-- Post Header -->
            <div class="p-4 border-b">
              <div class="flex items-center">
                <img
                  :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(post.user.name)}`"
                  :alt="post.user.name"
                  class="h-10 w-10 rounded-full"
                />
                <div class="ml-3">
                  <div class="font-semibold">{{ post.user.name }}</div>
                  <div class="text-gray-500 text-sm">
                    {{ new Date(post.created_at).toLocaleDateString() }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Caption -->
            <div class="p-4 border-b">
              <p class="text-gray-800">{{ post.caption }}</p>
            </div>

            <!-- Likes -->
            <div class="p-4 border-b">
              <button
                @click="toggleLike"
                :class="getLikeButtonClasses"
                :title="isLiked ? 'Click to unlike' : 'Click to like'"
              >
                <svg
                  v-if="!isLiked"
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-6 w-6 transition-all duration-200"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                  />
                </svg>
                <svg
                  v-else
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-6 w-6 transition-all duration-200"
                  viewBox="0 0 24 24"
                  fill="currentColor"
                >
                  <path
                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 10-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                  />
                </svg>
                <span>{{ post.likes.length }} likes</span>
              </button>
            </div>

            <!-- Comments Section -->
            <div class="flex-1 overflow-y-auto">
              <div class="p-4 space-y-4">
                <div
                  v-for="comment in post.comments"
                  :key="comment.id"
                  class="flex space-x-3"
                >
                  <img
                    :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(
                      comment.user.name
                    )}`"
                    :alt="comment.user.name"
                    class="h-8 w-8 rounded-full"
                  />
                  <div>
                    <div class="flex items-center space-x-2">
                      <span class="font-semibold">{{ comment.user.name }}</span>
                      <span class="text-gray-500 text-xs">
                        {{ new Date(comment.created_at).toLocaleDateString() }}
                      </span>
                    </div>
                    <p class="text-gray-800">{{ comment.content }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Add Comment Form -->
            <div class="p-4 border-t mt-auto">
              <form @submit.prevent="submitComment" class="flex">
                <input
                  v-model="commentForm"
                  type="text"
                  placeholder="Add a comment..."
                  class="flex-1 border rounded-l-lg px-4 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                />
                <button
                  type="submit"
                  class="px-4 py-2 bg-blue-500 text-white rounded-r-lg hover:bg-blue-600 disabled:opacity-50"
                >
                  Post
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
