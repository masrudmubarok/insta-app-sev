<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const isOpen = ref(false)

const logout = () => {
  router.post(route('logout'))
}

// Close dropdown when clicking outside
const closeDropdown = (e: Event) => {
  const target = e.target as HTMLElement
  if (!target.closest('.profile-dropdown')) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', closeDropdown)
})

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown)
})
</script>

<template>
  <div class="relative profile-dropdown">
    <button 
      @click="isOpen = !isOpen" 
      class="flex items-center space-x-2 focus:outline-none"
      aria-label="User menu"
    >
      <img 
        :src="`https://ui-avatars.com/api/?name=${encodeURIComponent($page.props.auth.user.name)}`"
        class="w-8 h-8 rounded-full border border-gray-200"
        :alt="$page.props.auth.user.name"
      />
      <div class="hidden md:flex items-center">
        <span class="text-sm font-medium text-gray-700">
          {{ $page.props.auth.user.name }}
        </span>
        <!-- Dropdown arrow -->
        <svg 
          class="w-4 h-4 ml-1 text-gray-500"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path 
            stroke-linecap="round" 
            stroke-linejoin="round" 
            stroke-width="2" 
            d="M19 9l-7 7-7-7"
          />
        </svg>
      </div>
    </button>

    <!-- Dropdown Menu -->
    <div v-show="isOpen" 
      class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 z-50"
    >
      <Link
        :href="route('profile.show')"
        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out"
      >
        <div class="flex items-center space-x-2">
          <svg 
            class="w-4 h-4" 
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
          >
            <path 
              stroke-linecap="round" 
              stroke-linejoin="round" 
              stroke-width="2" 
              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" 
            />
          </svg>
          <span>Profile</span>
        </div>
      </Link>

      <Link
        :href="route('posts.index')"
        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out"
      >
        <div class="flex items-center space-x-2">
          <svg 
            class="w-4 h-4" 
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
          >
            <path 
              stroke-linecap="round" 
              stroke-linejoin="round" 
              stroke-width="2" 
              d="M4 6h16M4 10h16M4 14h16M4 18h16" 
            />
          </svg>
          <span>Feed</span>
        </div>
      </Link>
      
      <div class="border-t border-gray-100 my-1"></div>
      
      <button
        @click="logout"
        class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 transition duration-150 ease-in-out"
      >
        <div class="flex items-center space-x-2">
          <svg 
            class="w-4 h-4" 
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
          >
            <path 
              stroke-linecap="round" 
              stroke-linejoin="round" 
              stroke-width="2" 
              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" 
            />
          </svg>
          <span>Logout</span>
        </div>
      </button>
    </div>
  </div>
</template>
