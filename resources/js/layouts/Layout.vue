<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ProfileDropdown from '@/components/ProfileDropdown.vue';

defineProps<{
  auth?: {
    user: {
      name: string;
    };
  };
}>();
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <Link href="/posts" class="flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 448 512">
                  <path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9 114.9-51.3 114.9-114.9-51.3-114.9-114.9-114.9zm0 189.6c-41.3 0-74.7-33.4-74.7-74.7s33.4-74.7 74.7-74.7 74.7 33.4 74.7 74.7-33.4 74.7-74.7 74.7zm146.4-194.3c0 14.9-12.1 27-27 27s-27-12.1-27-27 12.1-27 27-27 27 12.1 27 27zm76.1 27.2c-1.7-35.7-9.9-67.3-36.2-93.5C385.3 7.1 353.7-1.1 318 0.2 280.4-1.1 167.6-1.1 130 0.2 94.3-1.1 62.7 7.1 36.5 33.4 10.2 59.7 2 91.3.3 127 1.6 164.6 1.6 277.4.3 315c1.7 35.7 9.9 67.3 36.2 93.5 26.3 26.3 57.9 34.5 93.6 36.2 37.6 1.3 150.4 1.3 188 0 35.7-1.7 67.3-9.9 93.6-36.2 26.3-26.3 34.5-57.9 36.2-93.6 1.3-37.6 1.3-150.4 0-188zM398.8 388c-7.8 19.5-22.9 34.6-42.4 42.4-29.4 11.7-99.2 9-132.4 9s-103 2.6-132.4-9c-19.5-7.8-34.6-22.9-42.4-42.4-11.7-29.4-9-99.2-9-132.4s-2.6-103 9-132.4c7.8-19.5 22.9-34.6 42.4-42.4 29.4-11.7 99.2-9 132.4-9s103-2.6 132.4 9c19.5 7.8 34.6 22.9 42.4 42.4 11.7 29.4 9 99.2 9 132.4s2.6 103-9 132.4z"/>
              </svg>
              <span class="text-xl font-bold">InstaApp</span>
            </Link>
          </div>

          <div class="flex items-center">
            <ProfileDropdown />
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main>
      <slot></slot>
    </main>
  </div>
</template>
