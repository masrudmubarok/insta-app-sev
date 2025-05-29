<script setup lang="ts">
import { ref } from 'vue'
import Layout from '@/Layouts/Layout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'

const props = defineProps<{
  auth: {
    user: {
      id: number;
      name: string;
      email: string;
    };
  };
  posts: {
    data: Array<{
      id: number;
      image_path: string;
      caption: string;
      created_at: string;
      likes_count: number;
      comments_count: number;
    }>;
  };
}>();

const form = useForm({
  name: props.auth.user.name,
  email: props.auth.user.email,
});

const editMode = ref(false);

const updateProfile = () => {
  form.put(route('profile.update'), {
    preserveScroll: true,
    onSuccess: () => {
      editMode.value = false;
    },
  });
};
</script>

<template>
  <Head title="Profile" />

  <Layout>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
      <!-- Profile Header -->
      <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:px-6">
          <div class="flex items-center space-x-6">
            <img 
              :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(auth.user.name)}&size=128`"
              class="w-32 h-32 rounded-full border-2 border-gray-200"
              :alt="auth.user.name"
            />
            <div class="flex-1">
              <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">
                  {{ auth.user.name }}
                </h1>
                <button
                  @click="editMode = !editMode"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                  {{ editMode ? 'Cancel' : 'Edit Profile' }}
                </button>
              </div>
              <p class="mt-1 text-gray-500">{{ auth.user.email }}</p>
              
              <!-- Stats -->
              <div class="mt-4 flex space-x-6">
                <div>
                  <span class="font-bold">{{ posts.data.length }}</span>
                  <span class="text-gray-600"> posts</span>
                </div>
                <div>
                  <span class="font-bold">0</span>
                  <span class="text-gray-600"> followers</span>
                </div>
                <div>
                  <span class="font-bold">0</span>
                  <span class="text-gray-600"> following</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Edit Profile Form -->
        <div v-if="editMode" class="px-4 py-5 sm:px-6 border-t border-gray-200">
          <form @submit.prevent="updateProfile" class="space-y-4">
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700">
                Name
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              />
            </div>

            <div>
              <label for="email" class="block text-sm font-medium text-gray-700">
                Email
              </label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              />
            </div>

            <div class="flex justify-end">
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                Save Changes
              </button>
            </div>
          </form>
        </div>

        <!-- Posts Grid -->
        <div class="border-t border-gray-200">
          <div class="px-4 py-5 sm:px-6">
            <h2 class="text-lg font-medium text-gray-900">Posts</h2>
          </div>
          <div class="grid grid-cols-3 gap-1">
            <div
              v-for="post in posts.data"
              :key="post.id"
              class="aspect-square relative group cursor-pointer"
            >
              <Link :href="route('posts.show', post.id)">
                <img
                  :src="`/storage/${post.image_path}`"
                  :alt="post.caption"
                  class="w-full h-full object-cover"
                />
                <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center text-white">
                  <div class="flex space-x-6">
                    <div class="flex items-center">
                      <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                      </svg>
                      <span>{{ post.likes_count }}</span>
                    </div>
                    <div class="flex items-center">
                      <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                      </svg>
                      <span>{{ post.comments_count }}</span>
                    </div>
                  </div>
                </div>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>
