<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import Layout from '@/layouts/Layout.vue';
import { ref, computed } from 'vue';
import axios from 'axios';

defineOptions({
  layout: Layout,
});

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
}

interface Post {
  id: number;
  caption: string;
  image_path: string;
  created_at: string;
  likes: Like[];
  comments: Comment[];
  isLiked: boolean;
  user: {
    id: number;
    name: string;
  };
}

const props = defineProps<{
  posts: {
    data: Post[];
  };
}>();

const posts = ref<Post[]>(props.posts.data);
const commentForms = ref<{ [key: number]: string }>({});
const isSubmitting = ref(false);
const showImageAlert = ref(false);

const newPost = ref({
  caption: '',
  image: null as File | null,
});

const handleImageUpload = (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0];
  if (file) {
    newPost.value.image = file;
    showImageAlert.value = false;
  }
};

const submitPost = async (e: Event) => {
  if (!newPost.value.image) {
    showImageAlert.value = true;
    e.preventDefault();
    return;
  }
  
  if (isSubmitting.value) return;
  
  isSubmitting.value = true;
  showImageAlert.value = false;
  
  const formData = new FormData();
  formData.append('caption', newPost.value.caption);
  formData.append('image', newPost.value.image);

  try {
    const response = await axios.post('/posts', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'Accept': 'application/json'
      }
    });

    // Add the new post to the beginning of the posts array
    if (response.data) {
      posts.value.unshift(response.data);
    }

    // Reset the form
    newPost.value.caption = '';
    newPost.value.image = null;
    
    // Reset the file input
    const fileInput = document.querySelector('input[type="file"]') as HTMLInputElement;
    if (fileInput) {
      fileInput.value = '';
    }
  } catch (error) {
    console.error('Error creating post:', error);
  } finally {
    isSubmitting.value = false;
  }
};

const isPostLiked = computed(() => (post: Post) => {
  return post.isLiked;
});

const getLikeButtonClasses = computed(() => (post: Post) => {
  return [
    'group flex items-center space-x-1 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95',
    post.isLiked ? 'text-red-500 hover:text-red-600' : 'text-gray-500 hover:text-gray-600'
  ];
});

const likePost = async (postId: number) => {
  const post = posts.value.find(p => p.id === postId);
  if (!post) return;

  // Store current state for rollback
  const currentState = { likes: [...post.likes], isLiked: post.isLiked };
  
  // Optimistic update
  post.isLiked = !post.isLiked;
  if (post.isLiked) {
    post.likes.push({ user_id: user.value?.id });
  } else {
    post.likes = post.likes.filter(like => like.user_id !== user.value?.id);
  }

  try {
    const response = await axios.post(`/posts/${postId}/like`, null, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });
    
    // Update with server response to ensure consistency
    if (response.data) {
      post.likes = response.data.likes;
      post.isLiked = response.data.isLiked;
    }
  } catch (error: any) {
    // Revert to previous state if error occurs
    console.error('Error toggling like:', error.response?.data?.error || error.message);
    post.likes = currentState.likes;
    post.isLiked = currentState.isLiked;
    
    if (error.response?.status === 500) {
      console.error('Server error details:', error.response.data);
    }
  }
};

const submitComment = async (postId: number) => {
  const content = commentForms.value[postId];
  if (!content?.trim()) return;

  const post = posts.value.find(p => p.id === postId);
  if (!post) return;

  // Store current state for rollback
  const currentComments = [...post.comments];

  // Optimistic update
  const newComment = {
    id: Date.now(), // Temporary ID
    content: content,
    user: {
      id: user.value.id,
      name: user.value.name
    }
  };

  // Add comment optimistically
  post.comments.push(newComment);
  
  // Clear the form immediately for better UX
  commentForms.value[postId] = '';

  try {
    const response = await axios.post(`/posts/${postId}/comment`, {
      content: content
    });
    
    // Update with server response to ensure consistency
    if (response.data.comments) {
      post.comments = response.data.comments;
    }
  } catch (error) {
    console.error('Error posting comment:', error);
    // Revert to previous state if error occurs
    post.comments = currentComments;
    // Restore the form content in case of error
    commentForms.value[postId] = content;
  }
};

const getImageUrl = (imagePath: string) => {
  return `/storage/${imagePath}`;
};

const showPost = (postId: number) => {
  router.visit(`/posts/${postId}`, {
    preserveState: true,
    preserveScroll: true
  });
};
</script>

<template>
  <div>
    <Head title="Feed" />

    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      <!-- Create Post Form -->
      <div class="bg-white shadow rounded-lg p-6 mb-8">
        <form @submit.prevent="submitPost">
          <textarea
            v-model="newPost.caption"
            placeholder="What's on your mind?"
            class="w-full p-2 border rounded-lg mb-4"
            rows="3"
          ></textarea>
          
          <div class="flex flex-col space-y-4">
            <!-- Image Alert -->
            <div v-if="showImageAlert" class="text-red-500 text-sm flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
              Please attach an image to create a post
            </div>
            
            <div class="flex items-center justify-between">
              <!-- Custom File Input -->
              <label class="flex items-center space-x-2 cursor-pointer px-4 py-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm text-gray-700">Images</span>
                <input
                  type="file"
                  @change="handleImageUpload"
                  accept="image/*"
                  class="hidden"
                />
              </label>
              
              <button
                type="submit"
                :disabled="isSubmitting || !newPost.image"
                class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
              >
                <svg
                  v-if="isSubmitting"
                  class="animate-spin h-5 w-5 text-white"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  ></circle>
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  ></path>
                </svg>
                {{ isSubmitting ? 'Posting...' : 'Post' }}
              </button>
            </div>

            <!-- Selected Image Name -->
            <div v-if="newPost.image" class="text-sm text-gray-600 flex items-center space-x-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>{{ newPost.image.name }}</span>
            </div>
          </div>
        </form>
      </div>

      <!-- Posts Feed -->      
      <div class="space-y-8">
        <div 
          v-for="post in posts" 
          :key="post.id" 
          class="bg-white shadow rounded-lg"
        ><!-- Post Header -->
          <div class="p-4 border-b flex items-center">
            <!-- User Avatar -->
            <img
              :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(post.user.name)}`"
              :alt="post.user.name"
              class="w-10 h-10 rounded-full"
            />
            <div class="ml-3">
              <div class="font-semibold">{{ post.user.name }}</div>
              <div class="text-gray-500 text-sm">
                {{ new Date(post.created_at).toLocaleDateString() }}
              </div>
            </div>
          </div>

          <!-- Post Image with click handler -->
          <div class="cursor-pointer" @click="showPost(post.id)">
            <img
              :src="getImageUrl(post.image_path)"
              alt="Post"
              class="w-full object-cover max-h-[600px] hover:opacity-90 transition-opacity"
            />
          </div>

          <!-- Post Actions -->
          <div class="p-4 border-t border-b">
            <div class="flex items-center space-x-4">
              <button
                type="button"
                @click="likePost(post.id)"
                :class="getLikeButtonClasses(post)"
                :title="post.isLiked ? 'Click to unlike' : 'Click to like'"
              >
                <div class="relative">
                  <svg
                    v-if="!post.isLiked"
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-6 w-6 transition-all duration-200 group-hover:scale-110"
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
                    class="h-6 w-6 transition-all duration-200 group-hover:scale-110"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                  >
                    <path
                      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 10-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                    />
                  </svg>
                </div>
                <span>{{ post.likes.length }}</span>
              </button>
              <Link
                :href="`/posts/${post.id}`"
                class="flex items-center space-x-1 text-gray-500 hover:text-gray-700"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-6 w-6"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                  />
                </svg>
                <span>{{ post.comments.length }} comments</span>
              </Link>
            </div>
          </div>          <!-- Post Caption -->
          <div class="p-4">
            <p class="text-gray-800">
              <span class="font-bold">{{ post.user.name }}</span>
              <span class="ml-2">{{ post.caption }}</span>
            </p>
          </div>

          <!-- Comments (Preview) -->
          <div class="p-4 bg-gray-50">
            <div class="space-y-4">
              <div 
                v-for="comment in post.comments.slice(0, 2)" 
                :key="comment.id" 
                class="flex space-x-3"
              >
                <img
                  :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(comment.user.name)}`"
                  :alt="comment.user.name"
                  class="w-8 h-8 rounded-full"
                />
                <div>
                  <div class="font-semibold">{{ comment.user.name }}</div>
                  <p class="text-gray-600">{{ comment.content }}</p>
                </div>
              </div>
              
              <Link
                v-if="post.comments.length > 2"
                :href="`/posts/${post.id}`"
                class="text-sm text-gray-500 hover:text-gray-700"
              >
                View all {{ post.comments.length }} comments
              </Link>
            </div>            <!-- Add Comment -->
            <form
              @submit.prevent="submitComment(post.id)"
              class="mt-4 flex"
            >
              <input
                type="text"
                v-model="commentForms[post.id]"
                placeholder="Add a comment..."
                class="flex-1 border rounded-l-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              <button
                type="submit"
                :disabled="!commentForms[post.id]?.trim()"
                class="bg-blue-500 text-white px-4 py-2 rounded-r-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
              >
                Send
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes like {
  0% {
    transform: scale(1);
  }
  25% {
    transform: scale(1.2);
  }
  50% {
    transform: scale(0.95);
  }
  75% {
    transform: scale(1.1);
  }
  100% {
    transform: scale(1);
  }
}

.animate-like {
  animation: like 0.4s cubic-bezier(0.17, 0.89, 0.32, 1.49);
}
</style>
