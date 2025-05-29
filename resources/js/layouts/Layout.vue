<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ProfileDropdown from '@/Components/ProfileDropdown.vue';

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
