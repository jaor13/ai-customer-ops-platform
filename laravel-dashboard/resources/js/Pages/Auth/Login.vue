<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Sign In" />

        <div class="mb-6">
            <h2 class="text-xl font-extrabold font-display text-slate-900 tracking-tight">Welcome back</h2>
            <p class="mt-1 text-xs text-slate-500">Sign in to your operations dashboard</p>
        </div>

        <div v-if="status" class="mb-4 rounded-xl bg-emerald-50 border border-emerald-100 p-3 text-xs font-medium text-emerald-700">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="Email" class="text-slate-700 font-semibold" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1.5 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="you@company.com"
                />
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Password" class="text-slate-700 font-semibold" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1.5 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center cursor-pointer select-none">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-xs text-slate-500 font-medium">Remember me</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-xs text-slate-400 font-medium transition hover:text-blue-600"
                >
                    Forgot password?
                </Link>
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full justify-center rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 transition active:scale-[0.98]"
                    :class="{ 'opacity-50': form.processing }"
                    :disabled="form.processing"
                >
                    Sign In
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
