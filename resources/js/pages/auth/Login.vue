<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card } from '@/components/ui/card';
import { Link } from '@inertiajs/vue3';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthBase>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-center text-sm font-mediums">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-2">
            <div class="grid gap-2">
                <Input
                    id="email"
                    type="text"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="email"
                    v-model="form.email"
                    placeholder="Phone number, username, or email"
                    class="rounded-sm bg-zinc-50 border-zinc-200 px-2 py-3 text-sm"
                />
                <InputError :message="form.errors.email" />

                <Input
                    id="password"
                    type="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    v-model="form.password"
                    placeholder="Password"
                    class="rounded-sm bg-zinc-50 border-zinc-200 px-2 py-3 text-sm"
                />
                <InputError :message="form.errors.password" />

                <div v-if="canResetPassword" class="flex justify-start">
                    <a :href="route('password.request')" class="text-xs text-[#385185] hover:underline" :tabindex="4">
                        Forgot password?
                    </a>
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full rounded-lg py-1.5 text-sm font-semibold gradient-button"
                    :tabindex="3"
                    :disabled="form.processing"
                >
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Log in
                </Button>
            </div>

            <div class="mt-4 text-center text-xs text-zinc-500">
                <div class="space-x-1">
                    Don't have an account?
                    <Link :href="route('register')" class="text-blue-500 font-semibold hover:text-blue-600">
                        Sign up
                    </Link>
                </div>
            </div>
        </form>

    </AuthBase>
</template>

<style scoped>
.gradient-button {
    background: linear-gradient(to right, #9333ea, #db2777, #ec4899);
}
.gradient-button:hover {
    opacity: 0.9;
}
</style>
