<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import Layout from '@/layouts/Layout.vue';
import { ref, computed } from 'vue';
import axios from 'axios';
import { TransitionRoot, TransitionChild, Dialog, DialogTitle, DialogPanel, Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';

// Configure axios to include CSRF token
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;

defineOptions({
  layout: Layout,
});

const page = usePage();
interface Auth {
  user: {
    id: number;
    name: string;
  };
}

const user = ref((page.props.auth as Auth).user);

interface Like {
  user_id: number;
}

interface Comment {
  id: number;
  content: string;
  created_at: string;
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

interface Props {
  posts: {
    data: Post[];
  };
  users: {
    id: number;
    name: string;
    email: string;
  }[];
}

interface EditingPost extends Post {
  id: number;
  caption: string;
  image_path: string;
}

interface EditForm {
  caption: string;
  image: File | null;
}

const props = defineProps<Props>();
const users = ref(props.users);

const posts = ref<Post[]>(props.posts.data);
const commentForms = ref<{ [key: number]: string }>({});
const editingComment = ref<{ postId: number; commentId: number; content: string } | null>(null);
const isSubmitting = ref(false);
const showImageAlert = ref(false);
const isSavingEdit = ref(false);

const newPost = ref({
  caption: '',
  image: null as File | null,
});
const imagePreviewUrl = ref<string | null>(null);

const isModalOpen = ref(false);

const openPostModal = () => {
  isModalOpen.value = true;
};

const closePostModal = () => {
  isModalOpen.value = false;
  // Reset form
  newPost.value = {
    caption: '',
    image: null,
  };
  if (imagePreviewUrl.value) {
    URL.revokeObjectURL(imagePreviewUrl.value);
    imagePreviewUrl.value = null;
  }
};

const handleImageUpload = (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0];
  if (file) {
    // Validate file type
    if (!file.type.startsWith('image/')) {
      alert('Please select an image file');
      return;
    }

    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
      alert('Image size should be less than 5MB');
      return;
    }

    newPost.value.image = file;
    showImageAlert.value = false;
    
    // Create preview URL
    imagePreviewUrl.value = URL.createObjectURL(file);
  }
};

const removeImage = () => {
  newPost.value.image = null;
  if (imagePreviewUrl.value) {
    URL.revokeObjectURL(imagePreviewUrl.value);
    imagePreviewUrl.value = null;
  }
  const fileInput = document.querySelector('input[type="file"]') as HTMLInputElement;
  if (fileInput) {
    fileInput.value = '';
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

    // Reset form and close modal
    newPost.value.caption = '';
    newPost.value.image = null;
    
    if (imagePreviewUrl.value) {
      URL.revokeObjectURL(imagePreviewUrl.value);
      imagePreviewUrl.value = null;
    }
    
    const fileInput = document.querySelector('input[type="file"]') as HTMLInputElement;
    if (fileInput) {
      fileInput.value = '';
    }

    closePostModal();
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
    id: Date.now(),
    content: content,
    created_at: new Date().toISOString(),
    user: {
      id: user.value.id,
      name: user.value.name
    }
  };

  // Add comment optimistically
  post.comments.push(newComment);
  
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

const deleteComment = async (postId: number, commentId: number) => {
  const post = posts.value.find(p => p.id === postId);
  if (!post) return;

  // Store current comments for rollback
  const currentComments = [...post.comments];

  // Optimistic update
  post.comments = post.comments.filter(c => c.id !== commentId);
  try {
    await axios({
      method: 'DELETE',
      url: `/posts/${postId}/comments/${commentId}`,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      }
    });
  } catch (error: any) {
    console.error('Error deleting comment:', error.response?.data || error);
    // Revert to previous state if error occurs
    post.comments = currentComments;

    if (error.response?.status === 403) {
      alert('You are not authorized to delete this comment.');
    } else if (error.response?.status === 405) {
      console.error('Method not allowed. Please check route configuration.');
    } else {
      alert('Failed to delete comment. Please try again.');
    }
  }
};

const startEditComment = (postId: number, comment: Comment) => {
  editingComment.value = {
    postId,
    commentId: comment.id,
    content: comment.content
  };
};

const cancelEditComment = () => {
  editingComment.value = null;
};

const updateComment = async (postId: number, commentId: number, newContent: string) => {
  if (isSavingEdit.value || !newContent.trim()) return;
  
  const post = posts.value.find(p => p.id === postId);
  if (!post) return;

  const comment = post.comments.find(c => c.id === commentId);
  if (!comment) return;

  // Store current state for rollback
  const originalContent = comment.content;

  // Optimistic update
  comment.content = newContent;
  isSavingEdit.value = true;

  try {
    const response = await axios({
      method: 'PUT',
      url: `/posts/${postId}/comments/${commentId}`,
      data: { content: newContent.trim() },
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      }
    });
    
    if (response.data.comments) {
      post.comments = response.data.comments;
    }
  } catch (error: any) {
    console.error('Error updating comment:', error.response?.data || error);
    // Revert to previous state if error occurs
    comment.content = originalContent;

    if (error.response?.status === 403) {
      alert('You are not authorized to edit this comment.');
    } else if (error.response?.status === 405) {
      console.error('Method not allowed. Please check route configuration.');
    } else {
      alert('Failed to update comment. Please try again.');
    }
  } finally {
    isSavingEdit.value = false;
    editingComment.value = null;
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

const editingPost = ref<EditingPost | null>(null);
const editForm = ref<EditForm>({
  caption: '',
  image: null,
});
const editImagePreviewUrl = ref<string | null>(null);

const isEditModalOpen = ref(false);

const openEditModal = (post: Post) => {
  editingPost.value = post;
  editForm.value.caption = post.caption;
  editImagePreviewUrl.value = post.image_path ? getImageUrl(post.image_path) : null;
  isEditModalOpen.value = true;
};

const closeEditModal = () => {
  isEditModalOpen.value = false;
  editingPost.value = null;
  editForm.value = {
    caption: '',
    image: null,
  };
  editImagePreviewUrl.value = null;
};

const handleEditImageUpload = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  if (file) {
    if (!file.type.startsWith('image/')) {
      alert('Please select an image file');
      return;
    }

    if (file.size > 5 * 1024 * 1024) {
      alert('Image size should be less than 5MB');
      return;
    }

    editForm.value.image = file;
    editImagePreviewUrl.value = URL.createObjectURL(file);
  }
};

const submitEdit = async () => {
  if (isSubmitting.value || !editingPost.value) return;
  isSubmitting.value = true;

  const formData = new FormData();
  formData.append('caption', editForm.value.caption);
  if (editForm.value.image) {
    formData.append('image', editForm.value.image);
  }
  formData.append('_method', 'PUT');

  try {
    const response = await axios.post(`/posts/${editingPost.value.id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'Accept': 'application/json'
      }
    });

    // Update post in the list
    const index = posts.value.findIndex(p => p.id === editingPost.value?.id);
    if (index !== -1) {
      posts.value[index] = response.data.post;
    }

    closeEditModal();
  } catch (error) {
    console.error('Error updating post:', error);
    alert('Failed to update post. Please try again.');
  } finally {
    isSubmitting.value = false;
  }
};

const deletePost = async (postId: number) => {
  if (!confirm('Are you sure you want to delete this post?')) return;

  try {
    await axios.delete(`/posts/${postId}`);
    posts.value = posts.value.filter(p => p.id !== postId);
  } catch (error) {
    console.error('Error deleting post:', error);
    alert('Failed to delete post. Please try again.');
  }
};

const menuOpenPostId = ref<number | null>(null);

const toggleMenu = (postId: number) => {
  menuOpenPostId.value = menuOpenPostId.value === postId ? null : postId;
};
</script>

<template>
  <div>
    <Head title="Feed" />

    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      <!-- Stories Section -->
      <div class="bg-white shadow rounded-lg p-4 mb-8 overflow-x-auto">
        <div class="flex space-x-4 min-w-max">
          <div v-for="user in users" :key="user.id" class="flex flex-col items-center space-y-1">
            <div class="w-16 h-16 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 p-0.5">
              <img
                :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&size=64`"
                :alt="user.name"
                class="w-full h-full rounded-full object-cover border-2 border-white"
              />
            </div>
            <span class="text-xs text-gray-600 truncate w-16 text-center">{{ user.name }}</span>
          </div>
        </div>
      </div>

      <!-- Floating Action Button -->
      <button
        @click="openPostModal"
        class="fixed bottom-8 right-8 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-full px-4 py-2 shadow-lg hover:from-purple-600 hover:to-pink-600 transition-all duration-200 hover:scale-105 z-10 flex items-center space-x-2"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span>Post</span>
      </button>

      <!-- Create Post Modal -->
      <TransitionRoot appear :show="isModalOpen" as="template">
        <Dialog as="div" class="relative z-20" @close="closePostModal">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0"
            enter-to="opacity-100"
            leave="ease-in duration-200"
            leave-from="opacity-100"
            leave-to="opacity-0"
          >
            <div class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm transition-opacity" />
          </TransitionChild>

          <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
              <TransitionChild
                as="template"
                enter="ease-out duration-300"
                enter-from="opacity-0 scale-95 translate-y-4"
                enter-to="opacity-100 scale-100 translate-y-0"
                leave="ease-in duration-200"
                leave-from="opacity-100 scale-100 translate-y-0"
                leave-to="opacity-0 scale-95 translate-y-4"
              >
                <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white shadow-2xl transition-all">
                  <div class="relative">
                    <!-- Header with gradient background -->
                    <div class="bg-gradient-to-r from-purple-500 to-pink-500 px-6 py-4">
                      <DialogTitle as="h3" class="text-xl font-semibold text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Create New Post
                      </DialogTitle>
                    </div>

                    <!-- Close button -->
                    <button
                      @click="closePostModal"
                      class="absolute top-3 right-3 text-white hover:text-gray-200 transition-colors"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>

                    <!-- Form Content -->
                    <div class="p-6">
                      <form @submit.prevent="submitPost" class="space-y-6">
                        <!-- Image Upload Area -->
                        <div class="space-y-4">
                          <div
                            v-if="!imagePreviewUrl"
                            class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-purple-500 transition-colors"
                          >
                            <label class="cursor-pointer block">
                              <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                              </svg>
                              <span class="mt-2 block text-sm font-medium text-gray-900">
                                Drop your image here, or click to upload
                              </span>
                              <span class="mt-1 block text-sm text-gray-500">
                                PNG, JPG, GIF up to 5MB
                              </span>
                              <input
                                type="file"
                                @change="handleImageUpload"
                                accept="image/*"
                                class="hidden"
                              />
                            </label>
                          </div>

                          <!-- Image Preview -->
                          <div v-if="imagePreviewUrl" class="relative rounded-xl overflow-hidden">
                            <img 
                              :src="imagePreviewUrl" 
                              alt="Preview" 
                              class="max-h-96 w-full object-cover"
                            />
                            <button
                              @click="removeImage"
                              type="button"
                              class="absolute top-2 right-2 bg-black bg-opacity-50 hover:bg-opacity-75 text-white rounded-full p-2 transition-all duration-200 transform hover:scale-105"
                              title="Remove image"
                            >
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                              </svg>
                            </button>
                          </div>

                          <!-- Image Alert -->
                          <div v-if="showImageAlert" class="flex items-center text-red-500 text-sm rounded-lg p-4 bg-red-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            Please attach an image to create a post
                          </div>
                        </div>

                        <!-- Caption Input -->
                        <div class="space-y-2">
                          <label class="block text-sm font-medium text-gray-700 text-left">Caption</label>
                          <textarea
                            v-model="newPost.caption"
                            placeholder="Share your thoughts..."
                            class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all resize-none text-left"
                            rows="3"
                          ></textarea>
                        </div>

                        <!-- Submit Button -->
                        <button
                          type="submit"
                          :disabled="isSubmitting || !newPost.image"
                          class="w-full bg-gradient-to-r from-purple-500 to-pink-500 text-white py-3 px-4 rounded-xl font-medium hover:from-purple-600 hover:to-pink-600 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transform transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center space-x-2"
                        >
                          <svg
                            v-if="isSubmitting"
                            class="animate-spin h-5 w-5"
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
                          <span>{{ isSubmitting ? 'Sharing...' : 'Share Post' }}</span>
                        </button>
                      </form>
                    </div>
                  </div>
                </DialogPanel>
              </TransitionChild>
            </div>
          </div>
        </Dialog>
      </TransitionRoot>

      <!-- Posts Feed -->      
      <div class="space-y-8">
        <div 
          v-for="post in posts" 
          :key="post.id" 
          class="bg-white shadow rounded-lg"
        ><!-- Post Header -->
          <div class="p-4 border-b flex items-center justify-between">
            <div class="flex items-center">
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
            
            <!-- Post Actions Menu -->
            <Menu as="div" class="relative" v-if="post.user.id === user.id">
              <MenuButton 
                @click="toggleMenu(post.id)"
                class="flex items-center text-gray-400 hover:text-gray-600"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
              </MenuButton>

              <transition
                enter-active-class="transition duration-100 ease-out"
                enter-from-class="transform scale-95 opacity-0"
                enter-to-class="transform scale-100 opacity-100"
                leave-active-class="transition duration-75 ease-in"
                leave-from-class="transform scale-100 opacity-100"
                leave-to-class="transform scale-95 opacity-0"
              >
                <MenuItems 
                  v-if="menuOpenPostId === post.id"
                  class="absolute right-0 mt-2 w-48 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-10"
                >
                  <MenuItem v-slot="{ active }">
                    <button
                      @click="openEditModal(post)"
                      :class="['w-full text-left px-4 py-2 text-sm', active ? 'bg-gray-100 text-gray-900' : 'text-gray-700']"
                    >
                      Edit Post
                    </button>
                  </MenuItem>
                  <MenuItem v-slot="{ active }">
                    <button
                      @click="deletePost(post.id)"
                      :class="['w-full text-left px-4 py-2 text-sm', active ? 'bg-gray-100 text-red-600' : 'text-red-500']"
                    >
                      Delete Post
                    </button>
                  </MenuItem>
                </MenuItems>
              </transition>
            </Menu>
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
                      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 10-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                    />
                  </svg>
                  <svg
                    v-else
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-6 w-6 transition-all duration-200 group-hover:scale-110"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                    stroke="none"
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
                <span>{{ post.comments.length }}</span>
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
                  class="flex space-x-3 group"
                >
                  <img
                    :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(comment.user.name)}`"
                    :alt="comment.user.name"
                    class="w-8 h-8 rounded-full"
                  />
                  <div class="flex-grow">
                    <div class="font-semibold">{{ comment.user.name }}</div>
                    <div v-if="editingComment?.postId === post.id && editingComment?.commentId === comment.id" class="flex items-center space-x-2">
                      <input
                        type="text"
                        v-model="editingComment.content"
                        class="flex-grow border rounded px-2 py-1 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        @keyup.enter="updateComment(post.id, comment.id, editingComment.content)"
                        :disabled="isSavingEdit"
                      />
                      <button
                        @click="updateComment(post.id, comment.id, editingComment.content)"
                        class="text-purple-500 hover:text-purple-600 text-sm flex items-center space-x-1"
                        :disabled="isSavingEdit"
                      >
                        <svg v-if="isSavingEdit" class="animate-spin h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        {{ isSavingEdit ? 'Saving...' : 'Save' }}
                      </button>
                      <button
                        @click="cancelEditComment"
                        class="text-gray-500 hover:text-gray-600 text-sm ml-2"
                        :disabled="isSavingEdit"
                      >
                        Cancel
                      </button>
                    </div>
                    <p v-else class="text-gray-600">{{ comment.content }}</p>
                  </div>
                  <div v-if="comment.user.id === user.id && (!editingComment || editingComment.commentId !== comment.id)" class="opacity-0 group-hover:opacity-100 transition-opacity flex items-start space-x-2">
                    <button
                      @click="startEditComment(post.id, comment)"
                      class="text-gray-400 hover:text-gray-600 transition-colors duration-200"
                      title="Edit comment"
                      :disabled="editingComment !== null"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button
                      @click="deleteComment(post.id, comment.id)"
                      class="text-gray-400 hover:text-red-500 transition-colors duration-200"
                      title="Delete comment"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </div>
                
                <Link
                  v-if="post.comments.length > 2"
                  :href="`/posts/${post.id}`"
                  class="text-sm text-gray-500 hover:text-gray-700"
                >
                  View all {{ post.comments.length }} comments
                </Link>

                <!-- Add Comment Form -->
                <form @submit.prevent="submitComment(post.id)" class="mt-4 flex">
                  <input
                    v-model="commentForms[post.id]"
                    type="text"
                    placeholder="Add a comment..."
                    class="flex-1 border rounded-l-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                  />
                  <button
                    type="submit"
                    class="px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-r-lg hover:from-purple-600 hover:to-pink-600 disabled:opacity-50 transition-all duration-200"
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

  <!-- Edit Post Modal -->
  <TransitionRoot appear :show="isEditModalOpen" as="template">
    <Dialog as="div" class="relative z-20" @close="closeEditModal">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 scale-95 translate-y-4"
            enter-to="opacity-100 scale-100 translate-y-0"
            leave="ease-in duration-200"
            leave-from="opacity-100 scale-100 translate-y-0"
            leave-to="opacity-0 scale-95 translate-y-4"
          >
            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white shadow-2xl transition-all">
              <div class="relative">
                <!-- Header with gradient background -->
                <div class="bg-gradient-to-r from-purple-500 to-pink-500 px-6 py-4">
                  <DialogTitle as="h3" class="text-xl font-semibold text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Post
                  </DialogTitle>
                </div>

                <!-- Close button -->
                <button
                  @click="closeEditModal"
                  class="absolute top-3 right-3 text-white hover:text-gray-200 transition-colors"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>

                <!-- Form Content -->
                <div class="p-6">
                  <form @submit.prevent="submitEdit" class="space-y-6">
                    <!-- Image Preview -->
                    <div class="relative rounded-xl overflow-hidden">
                      <img 
                        v-if="editImagePreviewUrl"
                        :src="editImagePreviewUrl" 
                        :alt="editForm.caption" 
                        class="max-h-96 w-full object-cover"
                      />
                      <label class="absolute bottom-2 right-2 bg-black bg-opacity-50 hover:bg-opacity-75 text-white rounded-lg px-4 py-2 cursor-pointer transition-all duration-200">
                        Change Image
                        <input
                          type="file"
                          @change="handleEditImageUpload"
                          accept="image/*"
                          class="hidden"
                        />
                      </label>
                    </div>

                    <!-- Caption Input -->
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700 text-left">Caption</label>
                      <textarea
                        v-model="editForm.caption"
                        placeholder="Write a caption..."
                        class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all resize-none text-left"
                        rows="3"
                      ></textarea>
                    </div>

                    <!-- Submit Button -->
                    <button
                      type="submit"
                      :disabled="isSubmitting"
                      class="w-full bg-gradient-to-r from-purple-500 to-pink-500 text-white py-3 px-4 rounded-xl font-medium hover:from-purple-600 hover:to-pink-600 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transform transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center space-x-2"
                    >
                      <svg
                        v-if="isSubmitting"
                        class="animate-spin h-5 w-5"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                      >
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      <span>{{ isSubmitting ? 'Saving...' : 'Save Changes' }}</span>
                    </button>
                  </form>
                </div>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
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
