<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { Card, CardContent } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { User, Shield, AlertTriangle, Calendar, ShieldCheck, Mail } from 'lucide-vue-next';
import { computed } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = computed(() => usePage().props.auth.user);
const userInitials = computed(() => {
    if (!user.value?.name) return 'U';
    return user.value.name
        .split(' ')
        .map((n) => n.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2);
});

const joinedDate = computed(() => {
    if (!user.value?.created_at) return 'Recently';
    return new Date(user.value.created_at).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
    });
});

const scrollToSection = (id) => {
    const el = document.getElementById(id);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
};
</script>

<template>
    <Head title="Account Settings" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <h1 class="text-lg font-extrabold font-display text-text tracking-tight sm:text-xl">
                    Account Settings
                </h1>
            </div>
        </template>

        <div class="py-6 max-w-7xl mx-auto">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 items-start">
                
                <!-- LEFT COLUMN: User Summary & Navigation -->
                <div class="space-y-6 lg:sticky lg:top-24">
                    <!-- User Profile Summary Card -->
                    <Card class="overflow-hidden">
                        <!-- Gradient Banner -->
                        <div class="h-24 bg-gradient-to-r from-primary/80 to-blue-600 dark:from-primary/60 dark:to-blue-700/80 relative" />
                        
                        <CardContent class="pt-0 px-6 pb-6 relative flex flex-col items-center">
                            <!-- Avatar -->
                            <div class="relative -mt-12 mb-4">
                                <div class="h-20 w-20 rounded-full border-4 border-white bg-primary dark:border-surface text-lg font-black text-white flex items-center justify-center shadow-lg tracking-wider">
                                    {{ userInitials }}
                                </div>
                                <span class="absolute bottom-1 right-1 flex h-4 w-4 rounded-full border-2 border-white bg-emerald-500 dark:border-surface" title="Active session" />
                            </div>

                            <!-- Name / Email -->
                            <h2 class="text-sm font-extrabold text-text text-center">{{ user.name }}</h2>
                            <p class="text-xs text-text-tertiary mt-1 text-center truncate max-w-full flex items-center gap-1.5 justify-center">
                                <Mail class="h-3 w-3 shrink-0" />
                                {{ user.email }}
                            </p>

                            <!-- Role / Badge -->
                            <div class="mt-4 flex flex-wrap gap-2 justify-center">
                                <Badge variant="secondary" class="bg-primary-light text-primary hover:bg-primary-light border border-primary/10 text-[10px] font-bold px-2 py-0.5 rounded-lg flex items-center gap-1">
                                    <ShieldCheck class="h-3 w-3" />
                                    Administrator
                                </Badge>
                            </div>

                            <div class="w-full border-t border-border mt-5 pt-4 flex items-center justify-center gap-2 text-[10px] font-bold text-text-tertiary uppercase tracking-widest">
                                <Calendar class="h-3.5 w-3.5" />
                                <span>Joined {{ joinedDate }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Sidebar navigation widget -->
                    <Card class="hidden lg:block">
                        <CardContent class="p-3">
                            <ul class="space-y-1">
                                <li>
                                    <button 
                                        @click="scrollToSection('profile-details')"
                                        class="w-full flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-bold text-text-secondary hover:text-text hover:bg-surface-hover transition-all text-left"
                                    >
                                        <User class="h-4 w-4 text-text-tertiary" />
                                        Profile Details
                                    </button>
                                </li>
                                <li>
                                    <button 
                                        @click="scrollToSection('security-password')"
                                        class="w-full flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-bold text-text-secondary hover:text-text hover:bg-surface-hover transition-all text-left"
                                    >
                                        <Shield class="h-4 w-4 text-text-tertiary" />
                                        Security & Password
                                    </button>
                                </li>
                                <li>
                                    <button 
                                        @click="scrollToSection('danger-zone')"
                                        class="w-full flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-bold text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20 hover:text-red-500 transition-all text-left"
                                    >
                                        <AlertTriangle class="h-4 w-4 text-red-500" />
                                        Danger Zone
                                    </button>
                                </li>
                            </ul>
                        </CardContent>
                    </Card>
                </div>

                <!-- RIGHT COLUMN: Settings Forms -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Profile Information Form -->
                    <Card id="profile-details">
                        <CardContent class="p-6 sm:p-8">
                            <UpdateProfileInformationForm
                                :must-verify-email="mustVerifyEmail"
                                :status="status"
                            />
                        </CardContent>
                    </Card>

                    <!-- Password Form -->
                    <Card id="security-password">
                        <CardContent class="p-6 sm:p-8">
                            <UpdatePasswordForm />
                        </CardContent>
                    </Card>

                    <!-- Danger Zone Form -->
                    <Card id="danger-zone" class="border-red-500/20 dark:border-red-500/20 shadow-red-500/5 bg-gradient-to-b from-white to-red-50/10 dark:from-surface dark:to-red-950/5">
                        <CardContent class="p-6 sm:p-8">
                            <DeleteUserForm />
                        </CardContent>
                    </Card>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

