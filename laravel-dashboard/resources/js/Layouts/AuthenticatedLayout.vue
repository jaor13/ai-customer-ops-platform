<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    ShieldCheck,
    Users,
    Ticket as TicketIcon,
    UserCircle,
    BookOpen,
    Settings,
    LogOut,
    Bell,
    Menu,
    X,
    ChevronLeft,
    ChevronRight,
    Sun,
    Moon,
    Search,
    CheckCircle2,
    AlertCircle,
    Info,
} from 'lucide-vue-next';
import AppLogo from '@/Components/AppLogo.vue';

const page = usePage();

// ── Sidebar collapse (persisted) ────────────────────────────────
const isCollapsed = ref(localStorage.getItem('sidebar-collapsed') === 'true');
const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed.value;
    localStorage.setItem('sidebar-collapsed', isCollapsed.value);
};

// ── Mobile drawer ───────────────────────────────────────────────
const showMobileMenu = ref(false);

// ── Dark mode ──────────────────────────────────────────────────
const isDarkMode = ref(document.documentElement.classList.contains('dark'));
const toggleDarkMode = () => {
    document.documentElement.classList.toggle('dark');
    isDarkMode.value = document.documentElement.classList.contains('dark');
    localStorage.setItem('theme', isDarkMode.value ? 'dark' : 'light');
};

// ── User menu ──────────────────────────────────────────────────
const showUserDropdown = ref(false);

// ── Notifications dropdown ─────────────────────────────────────
const showNotificationDropdown = ref(false);

// ── Pending approvals badge (shared from server via Inertia props) ─
const pendingApprovalsCount = computed(
    () => page.props.pendingApprovalsCount ?? 0,
);

// ── Header notifications (shared from server) ──────────────────
const notifications = computed(() => page.props.notifications ?? []);
const notifIcon = (type) => (type === 'ticket' ? TicketIcon : ShieldCheck);

// ── Navigation (matches docs/07-laravel-dashboard.md) ──────────
const navigation = [
    { name: 'Dashboard',      route: 'dashboard',           icon: LayoutDashboard, group: 'Operations' },
    { name: 'Approvals',      route: 'approvals.index',     icon: ShieldCheck,     group: 'Operations', badgeKey: 'pending' },
    { name: 'Leads',          route: 'leads.index',         icon: Users,           group: 'Pipeline' },
    { name: 'Tickets',        route: 'tickets.index',       icon: TicketIcon,      group: 'Pipeline' },
    { name: 'Customers',      route: 'customers.index',     icon: UserCircle,      group: 'Pipeline' },
    { name: 'Knowledge Base', route: 'knowledge-base.index',icon: BookOpen,        group: 'Resources' },
];

const groupedNav = computed(() => {
    const groups = {};
    for (const item of navigation) {
        if (!groups[item.group]) groups[item.group] = [];
        groups[item.group].push(item);
    }
    return groups;
});

const isActive = (routeName) => {
    try {
        return route().current(routeName);
    } catch (e) {
        return false;
    }
};

// ── Close dropdowns on outside click ───────────────────────────
const handleClickOutside = (e) => {
    if (!e.target.closest('[data-user-menu]')) showUserDropdown.value = false;
    if (!e.target.closest('[data-notif-menu]')) showNotificationDropdown.value = false;
};

onMounted(() => window.addEventListener('click', handleClickOutside));
onUnmounted(() => window.removeEventListener('click', handleClickOutside));

// ── Global toast system ───────────────────────────────────────────
const toast = ref({ visible: false, msg: '', type: 'success' });
const showToast = (msg, type = 'success') => {
    toast.value = { visible: true, msg, type };
    setTimeout(() => {
        if (toast.value.msg === msg) {
            toast.value.visible = false;
        }
    }, 4000);
};

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            showToast(flash.success, 'success');
            page.props.flash.success = null;
        }
        if (flash?.error) {
            showToast(flash.error, 'error');
            page.props.flash.error = null;
        }
    },
    { deep: true, immediate: true }
);
</script>

<template>
    <div class="min-h-screen bg-page text-text font-sans flex relative selection:bg-blue-500 selection:text-white">
        <!-- subtle background dot pattern -->
        <div class="fixed inset-0 bg-dot-pattern opacity-[0.4] pointer-events-none" />

        <!-- ── DESKTOP SIDEBAR ─────────────────────────────────── -->
        <aside
            class="fixed inset-y-0 left-0 z-40 hidden border-r border-border bg-white/70 backdrop-blur-md lg:flex lg:flex-col transition-all duration-300 dark:bg-surface/70"
            :class="isCollapsed ? 'w-16' : 'w-60'"
        >
            <!-- collapse toggle -->
            <button
                @click="toggleSidebar"
                class="absolute -right-3 top-5 z-50 flex h-6 w-6 items-center justify-center rounded-full border border-border bg-white text-text-tertiary hover:text-text shadow-md transition dark:bg-surface"
                :aria-label="isCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
            >
                <ChevronLeft v-if="!isCollapsed" class="h-3.5 w-3.5" />
                <ChevronRight v-else class="h-3.5 w-3.5" />
            </button>

            <!-- brand -->
            <div class="flex h-16 items-center gap-2.5 border-b border-border px-4 shrink-0">
                <AppLogo size="sm" />
                <div v-if="!isCollapsed" class="min-w-0">
                    <p class="text-xs font-extrabold font-display text-text leading-tight truncate">AI Ops</p>
                    <p class="text-[9px] font-bold uppercase tracking-widest text-text-tertiary mt-0.5">Customer Console</p>
                </div>
            </div>

            <!-- nav -->
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
                <div v-for="(items, group) in groupedNav" :key="group">
                    <p
                        v-if="!isCollapsed"
                        class="px-3 mb-2 text-[9px] font-bold uppercase tracking-widest text-text-tertiary font-mono"
                    >
                        {{ group }}
                    </p>
                    <ul class="space-y-1">
                        <li v-for="item in items" :key="item.name">
                            <Link
                                :href="route(item.route)"
                                :class="[
                                    'flex items-center rounded-xl border border-transparent px-3 py-2 text-xs font-bold transition-all',
                                    isActive(item.route)
                                        ? 'bg-primary-light text-primary border-primary/20'
                                        : 'text-text-secondary hover:text-text hover:bg-surface-hover',
                                    isCollapsed ? 'justify-center' : 'gap-3',
                                ]"
                                :title="isCollapsed ? item.name : undefined"
                            >
                                <component :is="item.icon" class="h-4 w-4 shrink-0" />
                                <span v-if="!isCollapsed" class="truncate flex-1">{{ item.name }}</span>
                                <span
                                    v-if="!isCollapsed && item.badgeKey === 'pending' && pendingApprovalsCount > 0"
                                    class="flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[8px] font-black text-white"
                                >
                                    {{ pendingApprovalsCount }}
                                </span>
                                <span
                                    v-else-if="isCollapsed && item.badgeKey === 'pending' && pendingApprovalsCount > 0"
                                    class="absolute ml-5 -mt-4 h-1.5 w-1.5 rounded-full bg-red-500"
                                />
                            </Link>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- user footer -->
            <div class="relative border-t border-border p-3" data-user-menu>
                <button
                    @click="showUserDropdown = !showUserDropdown"
                    class="flex items-center w-full rounded-xl p-1.5 hover:bg-surface-hover transition"
                    :class="isCollapsed ? 'justify-center' : 'gap-2.5'"
                >
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-white">
                        {{ page.props.auth.user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div v-if="!isCollapsed" class="min-w-0 flex-1 text-left">
                        <p class="text-[11px] font-extrabold text-text leading-tight truncate">{{ page.props.auth.user.name }}</p>
                        <p class="text-[9px] text-text-tertiary leading-none mt-0.5 truncate">{{ page.props.auth.user.email }}</p>
                    </div>
                </button>

                <transition name="fade">
                    <div
                        v-if="showUserDropdown"
                        class="absolute bottom-full left-3 right-3 mb-2 z-50 rounded-2xl border border-border bg-white p-2 shadow-xl dark:bg-surface space-y-1"
                    >
                        <Link
                            :href="route('profile.edit')"
                            class="flex items-center gap-2 rounded-xl px-2.5 py-2 text-xs font-semibold text-text-secondary hover:text-text hover:bg-surface-hover w-full"
                        >
                            <Settings class="h-4 w-4" />
                            Account Settings
                        </Link>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="flex items-center gap-2 rounded-xl px-2.5 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 w-full text-left dark:text-red-400 dark:hover:bg-red-500/10"
                        >
                            <LogOut class="h-4 w-4" />
                            Sign Out
                        </Link>
                    </div>
                </transition>
            </div>
        </aside>

        <!-- ── MAIN AREA ──────────────────────────────────────── -->
        <div
            class="flex-1 flex flex-col min-w-0 transition-all duration-300"
            :class="isCollapsed ? 'lg:pl-16' : 'lg:pl-60'"
        >
            <!-- top bar -->
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-border bg-white/70 backdrop-blur-md px-4 sm:px-6 dark:bg-surface/70">
                <div class="flex items-center gap-3">
                    <button
                        @click="showMobileMenu = true"
                        class="rounded-xl p-2 text-text-secondary hover:bg-surface-hover lg:hidden"
                        aria-label="Open menu"
                    >
                        <Menu class="h-5 w-5" />
                    </button>

                    <slot name="header">
                        <div class="hidden sm:flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-widest text-text-tertiary">
                            <span>Workspace</span>
                            <span>/</span>
                            <span class="text-text-secondary font-extrabold">{{ $page.props.title || 'Console' }}</span>
                        </div>
                    </slot>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    <span class="hidden md:inline-flex items-center gap-1.5 rounded-full border border-emerald-100 bg-emerald-50 px-2.5 py-0.5 text-[9px] font-bold uppercase tracking-widest text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse" />
                        AI Active
                    </span>

                    <button
                        @click="toggleDarkMode"
                        class="rounded-xl p-2 text-text-secondary hover:bg-surface-hover hover:text-text transition"
                        :aria-label="isDarkMode ? 'Switch to light mode' : 'Switch to dark mode'"
                    >
                        <Sun v-if="isDarkMode" class="h-4 w-4 text-amber-500" />
                        <Moon v-else class="h-4 w-4" />
                    </button>

                    <div class="relative" data-notif-menu>
                        <button
                            @click="showNotificationDropdown = !showNotificationDropdown"
                            class="relative rounded-xl p-2 text-text-secondary hover:bg-surface-hover hover:text-text transition"
                            aria-label="Notifications"
                        >
                            <Bell class="h-4 w-4" />
                            <span
                                v-if="notifications.length > 0"
                                class="absolute top-1.5 right-1.5 h-1.5 w-1.5 rounded-full bg-red-500"
                            />
                        </button>

                        <transition name="fade">
                            <div
                                v-if="showNotificationDropdown"
                                class="absolute right-0 mt-2 z-50 w-80 rounded-2xl border border-border bg-white/95 backdrop-blur-md p-4 shadow-xl dark:bg-surface/95"
                            >
                                <div class="flex items-center justify-between border-b border-border pb-2.5 mb-2">
                                    <h3 class="text-xs font-bold text-text">Notifications</h3>
                                    <span v-if="notifications.length > 0" class="text-[9px] font-bold text-primary">
                                        {{ notifications.length }} new
                                    </span>
                                </div>

                                <!-- Notification list -->
                                <ul v-if="notifications.length > 0" class="space-y-1 max-h-80 overflow-y-auto">
                                    <li v-for="n in notifications" :key="n.id">
                                        <Link
                                            :href="route(n.route)"
                                            class="flex items-start gap-3 rounded-xl p-2 hover:bg-surface-hover transition"
                                            @click="showNotificationDropdown = false"
                                        >
                                            <div
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg"
                                                :class="n.type === 'ticket'
                                                    ? 'bg-warning/10 text-warning'
                                                    : 'bg-primary-light text-primary'"
                                            >
                                                <component :is="notifIcon(n.type)" class="h-3.5 w-3.5" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-[11px] font-bold text-text leading-tight">{{ n.title }}</p>
                                                <p class="text-[10px] text-text-secondary truncate">{{ n.detail }}</p>
                                                <p class="text-[9px] text-text-tertiary mt-0.5">{{ n.time }}</p>
                                            </div>
                                        </Link>
                                    </li>
                                </ul>

                                <!-- Empty state -->
                                <div v-else class="py-4 flex flex-col items-center justify-center text-center gap-2">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 dark:bg-surface-hover text-text-tertiary">
                                        <Bell class="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-text">All caught up!</p>
                                        <p class="text-[10px] text-text-tertiary mt-0.5">No new notifications at this time.</p>
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </div>
                </div>
            </header>

            <!-- page slot -->
            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8 relative z-10 overflow-y-auto">
                <slot />
            </main>
        </div>

        <!-- ── MOBILE DRAWER ──────────────────────────────────── -->
        <transition name="fade">
            <div
                v-if="showMobileMenu"
                class="fixed inset-0 z-50 lg:hidden bg-slate-950/40 backdrop-blur-sm"
                @click="showMobileMenu = false"
            >
                <div
                    class="absolute inset-y-0 left-0 w-64 flex flex-col border-r border-border bg-white p-4 dark:bg-surface"
                    @click.stop
                >
                    <div class="flex items-center justify-between border-b border-border pb-3 mb-4">
                        <div class="flex items-center gap-2.5">
                            <AppLogo size="sm" />
                            <span class="text-xs font-extrabold font-display text-text">AI Ops</span>
                        </div>
                        <button @click="showMobileMenu = false" class="rounded-lg p-1 text-text-secondary hover:bg-surface-hover" aria-label="Close menu">
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <nav class="flex-1 overflow-y-auto space-y-5">
                        <div v-for="(items, group) in groupedNav" :key="group">
                            <p class="px-3 mb-1 text-[9px] font-bold uppercase tracking-widest text-text-tertiary">{{ group }}</p>
                            <ul class="space-y-1">
                                <li v-for="item in items" :key="item.name">
                                    <Link
                                        :href="route(item.route)"
                                        :class="[
                                            'flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-bold transition',
                                            isActive(item.route)
                                                ? 'bg-primary-light text-primary'
                                                : 'text-text-secondary hover:text-text hover:bg-surface-hover',
                                        ]"
                                        @click="showMobileMenu = false"
                                    >
                                        <component :is="item.icon" class="h-4 w-4 shrink-0" />
                                        <span class="flex-1 truncate">{{ item.name }}</span>
                                        <span
                                            v-if="item.badgeKey === 'pending' && pendingApprovalsCount > 0"
                                            class="flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[8px] font-black text-white"
                                        >
                                            {{ pendingApprovalsCount }}
                                        </span>
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </nav>
                </div>
            </div>
        </transition>

        <!-- Global Toast -->
        <transition name="fade">
            <div
                v-if="toast.visible"
                :class="[
                    'fixed right-6 bottom-6 z-50 flex items-center gap-3 rounded-2xl border px-4 py-3 shadow-xl backdrop-blur-md transition-all duration-300',
                    toast.type === 'success' ? 'border-success/20 bg-success/10 text-success' :
                    toast.type === 'error' ? 'border-danger/20 bg-danger/10 text-danger' :
                    'border-primary/20 bg-primary-light text-primary',
                ]"
            >
                <component
                    :is="toast.type === 'success' ? CheckCircle2 : toast.type === 'error' ? AlertCircle : Info"
                    class="h-4 w-4 shrink-0"
                />
                <span class="text-xs font-bold">{{ toast.msg }}</span>
                <button
                    @click="toast.visible = false"
                    class="ml-1 rounded-lg p-0.5 text-text-secondary hover:bg-surface-hover hover:text-text transition"
                    aria-label="Close notification"
                >
                    <X class="h-3.5 w-3.5" />
                </button>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 150ms ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
