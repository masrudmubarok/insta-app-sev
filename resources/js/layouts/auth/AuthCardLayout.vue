<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Card, CardContent } from '@/components/ui/card';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-svh flex-col items-center justify-center bg-zinc-50 p-6 md:p-10">
        <div class="flex w-full max-w-sm flex-col gap-4">
            <Card class="border border-zinc-300 bg-white px-10 py-8 rounded-sm">
                <div class="mb-8 flex justify-center">
                    <Link :href="route('home')" class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 448 512">
                            <path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9 114.9-51.3 114.9-114.9-51.3-114.9-114.9-114.9zm0 189.6c-41.3 0-74.7-33.4-74.7-74.7s33.4-74.7 74.7-74.7 74.7 33.4 74.7 74.7-33.4 74.7-74.7 74.7zm146.4-194.3c0 14.9-12.1 27-27 27s-27-12.1-27-27 12.1-27 27-27 27 12.1 27 27zm76.1 27.2c-1.7-35.7-9.9-67.3-36.2-93.5C385.3 7.1 353.7-1.1 318 0.2 280.4-1.1 167.6-1.1 130 0.2 94.3-1.1 62.7 7.1 36.5 33.4 10.2 59.7 2 91.3.3 127 1.6 164.6 1.6 277.4.3 315c1.7 35.7 9.9 67.3 36.2 93.5 26.3 26.3 57.9 34.5 93.6 36.2 37.6 1.3 150.4 1.3 188 0 35.7-1.7 67.3-9.9 93.6-36.2 26.3-26.3 34.5-57.9 36.2-93.6 1.3-37.6 1.3-150.4 0-188zM398.8 388c-7.8 19.5-22.9 34.6-42.4 42.4-29.4 11.7-99.2 9-132.4 9s-103 2.6-132.4-9c-19.5-7.8-34.6-22.9-42.4-42.4-11.7-29.4-9-99.2-9-132.4s-2.6-103 9-132.4c7.8-19.5 22.9-34.6 42.4-42.4 29.4-11.7 99.2-9 132.4-9s103-2.6 132.4 9c19.5 7.8 34.6 22.9 42.4 42.4 11.7 29.4 9 99.2 9 132.4s2.6 103-9 132.4z"/>
                        </svg>

                        <span class="text-xl font-semibold text-zinc-800">InstaApp</span>
                    </Link>
                </div>
                
                <CardContent class="p-0">
                    <slot />
                </CardContent>
            </Card>

            <div class="mt-4 space-y-4">
                <div class="text-center text-xs text-zinc-500 space-x-4">
                    <a href="#" class="hover:underline">About</a>
                    <a href="#" class="hover:underline">Blog</a>
                    <a href="#" class="hover:underline">Jobs</a>
                    <a href="#" class="hover:underline">Help</a>
                    <a href="#" class="hover:underline">Privacy</a>
                    <a href="#" class="hover:underline">Terms</a>
                    <a href="#" class="hover:underline">Locations</a>
                </div>

                <div class="text-center text-xs text-zinc-500">
                    © {{ new Date().getFullYear() }} Instagram Clone
                </div>
            </div>
        </div>
    </div>
</template>
