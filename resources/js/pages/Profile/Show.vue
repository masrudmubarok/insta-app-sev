<script setup lang="ts">
import { ref } from 'vue'
import Layout from '@/layouts/Layout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'
import { TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'

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
      is_liked: boolean;
    }>;
  };
}>();

const form = useForm({
  name: props.auth.user.name,
  email: props.auth.user.email,
});

const isModalOpen = ref(false);

const openModal = () => {
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

const updateProfile = () => {
  form.put(route('profile.update'), {
    preserveScroll: true,
    onSuccess: () => {
      closeModal();
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
                  @click="openModal"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
                >
                  Edit Profile
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

        <!-- Edit Profile Modal -->
        <TransitionRoot appear :show="isModalOpen" as="template">
          <Dialog as="div" @close="closeModal" class="relative z-50">
            <TransitionChild
              as="template"
              enter="duration-300 ease-out"
              enter-from="opacity-0"
              enter-to="opacity-100"
              leave="duration-200 ease-in"
              leave-from="opacity-100"
              leave-to="opacity-0"
            >
              <div class="fixed inset-0 bg-black bg-opacity-25" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
              <div class="flex min-h-full items-center justify-center p-4 text-center">
                <TransitionChild
                  as="template"
                  enter="duration-300 ease-out"
                  enter-from="opacity-0 scale-95"
                  enter-to="opacity-100 scale-100"
                  leave="duration-200 ease-in"
                  leave-from="opacity-100 scale-100"
                  leave-to="opacity-0 scale-95"
                >
                  <DialogPanel class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                    <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900">
                      Edit Profile
                    </DialogTitle>
                    <div class="mt-2">
                      <p class="text-sm text-gray-500">
                        Update your profile information and save your changes.
                      </p>
                    </div>

                    <div class="mt-6">
                      <form @submit.prevent="updateProfile" class="space-y-6">
                        <div class="space-y-6">
                          <!-- Name Field -->
                          <div>
                            <label for="name" class="block text-sm font-medium text-gray-900">
                              Name
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                              <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="block w-full rounded-md border-gray-300 pl-3 pr-10 py-2 text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Enter your name"
                              />
                              <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none" v-if="form.errors.name">
                                <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                              </div>
                            </div>
                            <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</p>
                          </div>

                          <!-- Email Field -->
                          <div>
                            <label for="email" class="block text-sm font-medium text-gray-900">
                              Email Address
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                              <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="block w-full rounded-md border-gray-300 pl-3 pr-10 py-2 text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="you@example.com"
                              />
                              <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none" v-if="form.errors.email">
                                <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                              </div>
                            </div>
                            <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</p>
                            <p class="mt-2 text-xs text-gray-500">This email will be used for notifications and account recovery.</p>
                          </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                          <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
                          >
                            Cancel
                          </button>
                          <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
                            :class="{ 'opacity-75 cursor-not-allowed': form.processing }"
                          >
                            <svg
                              v-if="form.processing"
                              class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                              xmlns="http://www.w3.org/2000/svg"
                              fill="none"
                              viewBox="0 0 24 24"
                            >
                              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                          </button>
                        </div>
                      </form>
                    </div>
                  </DialogPanel>
                </TransitionChild>
              </div>
            </div>
          </Dialog>
        </TransitionRoot>

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
                <div class="absolute inset-0 bg-black bg-opacity-30 opacity-0 group-hover:opacity-70 backdrop-blur-[2px] transition-all duration-300 ease-in-out flex items-center justify-center text-white">
                  <div class="flex space-x-6">
                    <div class="flex items-center">                      <svg 
                        class="w-6 h-6 mr-2" 
                        :fill="post.is_liked ? 'rgb(239 68 68)' : 'none'" 
                        :stroke="post.is_liked ? 'rgb(239 68 68)' : 'currentColor'" 
                        viewBox="0 0 24 24"
                      >
                        <path 
                          stroke-linecap="round" 
                          stroke-linejoin="round" 
                          stroke-width="2" 
                          d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" 
                        />
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
