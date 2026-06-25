<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router, usePoll, usePage } from '@inertiajs/vue3';
import {
    Check,
    X,
    Mail,
    FileText,
    Clock,
    AlertTriangle,
    Inbox,
} from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
    CardFooter,
} from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';

const props = defineProps({
    approvals: { type: Array, default: () => [] },
    demoMode: { type: Boolean, default: false },
});

const page = usePage();
const localList = ref([...props.approvals]);
const selectedId = ref(localList.value[0]?.id ?? null);
const editedBody = ref('');
const isSubmitting = ref(false);
const showRejectModal = ref(false);
const rejectionReason = ref('');

const selected = computed(() =>
    localList.value.find((a) => a.id === selectedId.value) ?? null,
);

watch(
    selected,
    (val) => {
        editedBody.value = val ? val.edited_body || val.draft_body : '';
    },
    { immediate: true },
);

// Live updates — pull newly-queued drafts in without a reload (skip in demo
// mode). usePoll pauses automatically when the tab is backgrounded.
usePoll(20000, { only: ['approvals'] }, { autoStart: !props.demoMode });

// Merge incoming drafts into the local list, preserving existing item object
// references so the current selection and the in-progress edit are untouched.
watch(
    () => props.approvals,
    (incoming) => {
        if (props.demoMode) return;
        const existingIds = new Set(localList.value.map((a) => a.id));
        for (const item of incoming) {
            if (!existingIds.has(item.id)) localList.value.push(item);
        }
        if (selectedId.value === null && localList.value.length) {
            selectedId.value = localList.value[0].id;
        }
    },
);

const priorityVariant = (p) => {
    const map = {
        CRITICAL: 'danger',
        HIGH: 'warning',
        MEDIUM: 'default',
        LOW: 'secondary',
    };
    return map[p] || 'secondary';
};

const showToast = (msg, type = 'success') => {
    if (type === 'error') {
        page.props.flash.error = msg;
    } else {
        page.props.flash.success = msg;
    }
};

const removeFromList = (id) => {
    localList.value = localList.value.filter((a) => a.id !== id);
    if (selectedId.value === id) {
        selectedId.value = localList.value[0]?.id ?? null;
    }
};

const approve = () => {
    if (!selected.value) return;

    if (props.demoMode) {
        const id = selected.value.id;
        const name = selected.value.customer_name;
        isSubmitting.value = true;
        setTimeout(() => {
            isSubmitting.value = false;
            removeFromList(id);
            showToast(`Draft for ${name} approved and dispatched.`);
        }, 700);
        return;
    }

    isSubmitting.value = true;
    router.post(
        route('approvals.approve', selected.value.id),
        { edited_body: editedBody.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                isSubmitting.value = false;
                removeFromList(selected.value?.id);
                showToast('Draft approved and queued for dispatch.');
            },
            onError: () => {
                isSubmitting.value = false;
                showToast('Approval failed. Try again.', 'error');
            },
        },
    );
};

const openReject = () => {
    rejectionReason.value = '';
    showRejectModal.value = true;
};

const confirmReject = () => {
    if (!selected.value) return;
    showRejectModal.value = false;

    if (props.demoMode) {
        const id = selected.value.id;
        isSubmitting.value = true;
        setTimeout(() => {
            isSubmitting.value = false;
            removeFromList(id);
            showToast('Draft archived.', 'info');
        }, 600);
        return;
    }

    isSubmitting.value = true;
    router.post(
        route('approvals.reject', selected.value.id),
        { rejection_reason: rejectionReason.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                isSubmitting.value = false;
                removeFromList(selected.value?.id);
                showToast('Draft archived.', 'info');
            },
            onError: () => {
                isSubmitting.value = false;
                showToast('Rejection failed.', 'error');
            },
        },
    );
};
</script>

<template>
    <Head title="Approvals" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <h1 class="text-lg font-extrabold font-display text-text tracking-tight sm:text-xl">
                    Approvals Queue
                </h1>
                <Badge v-if="localList.length > 0" variant="danger">
                    {{ localList.length }} pending
                </Badge>
                <Badge v-else variant="success">All clear</Badge>
            </div>
        </template>

        <div class="max-w-7xl mx-auto">
            <!-- Empty state -->
            <Card v-if="localList.length === 0" class="text-center">
                <CardContent class="py-16 flex flex-col items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <Inbox class="h-7 w-7" />
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold font-display text-text">Queue is empty</h3>
                        <p class="text-xs text-text-secondary mt-1">
                            All AI drafts have been reviewed. New incoming emails will appear here.
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Split view -->
            <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Queue list -->
                <Card class="lg:col-span-4">
                    <CardHeader>
                        <CardTitle>Pending Drafts</CardTitle>
                        <CardDescription>Click an item to review</CardDescription>
                    </CardHeader>
                    <CardContent class="p-0">
                        <ul class="divide-y divide-border">
                            <li v-for="item in localList" :key="item.id">
                                <button
                                    @click="selectedId = item.id"
                                    :class="[
                                        'flex flex-col items-start w-full gap-2 px-5 py-3.5 text-left transition',
                                        selectedId === item.id
                                            ? 'bg-primary-light/60 dark:bg-primary/10'
                                            : 'hover:bg-surface-hover',
                                    ]"
                                >
                                    <div class="flex w-full items-center justify-between gap-2">
                                        <span class="text-xs font-extrabold text-text truncate">{{ item.customer_name }}</span>
                                        <Badge :variant="priorityVariant(item.priority)">{{ item.priority }}</Badge>
                                    </div>
                                    <p class="text-[11px] text-text-secondary truncate w-full">{{ item.subject }}</p>
                                    <div class="flex w-full items-center justify-between text-[9px] font-bold uppercase tracking-widest text-text-tertiary">
                                        <span class="flex items-center gap-1">
                                            <Mail class="h-2.5 w-2.5" />
                                            {{ item.category }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <Clock class="h-2.5 w-2.5" />
                                            {{ item.created_at }}
                                        </span>
                                    </div>
                                </button>
                            </li>
                        </ul>
                    </CardContent>
                </Card>

                <!-- Detail panel -->
                <div class="lg:col-span-8 space-y-4">
                    <Card v-if="selected">
                        <CardHeader>
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <CardTitle>{{ selected.subject }}</CardTitle>
                                    <CardDescription class="mt-1 truncate">
                                        {{ selected.customer_name }} · {{ selected.customer_email }}
                                    </CardDescription>
                                </div>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <Badge variant="secondary" class="font-mono">
                                        {{ selected.type === 'lead_welcome' ? 'Lead Welcome' : 'Ticket Reply' }}
                                    </Badge>
                                    <Badge :variant="priorityVariant(selected.priority)">{{ selected.priority }}</Badge>
                                    <Badge variant="secondary">{{ selected.category }}</Badge>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <!-- Original email / lead context -->
                            <section>
                                <h4 class="text-[9px] font-bold uppercase tracking-widest text-text-tertiary mb-2">
                                    {{ selected.type === 'lead_welcome' ? 'Lead details' : 'Original message' }}
                                </h4>
                                <div class="rounded-xl border border-border bg-surface-hover/40 p-4 text-xs text-text-secondary leading-relaxed whitespace-pre-line">
                                    {{ selected.body }}
                                </div>
                            </section>

                            <!-- AI draft (editable) -->
                            <section>
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-[9px] font-bold uppercase tracking-widest text-text-tertiary">
                                        AI draft response
                                    </h4>
                                    <Badge variant="default" class="text-[9px]">Editable</Badge>
                                </div>
                                <textarea
                                    v-model="editedBody"
                                    rows="8"
                                    class="w-full rounded-xl border border-border bg-white text-xs text-text leading-relaxed focus:border-primary focus:ring-primary/20 focus:ring-2 placeholder-text-tertiary p-4 dark:bg-surface"
                                    placeholder="AI draft response..."
                                />
                            </section>

                            <!-- Sources -->
                            <section v-if="selected.context_sources?.length">
                                <h4 class="text-[9px] font-bold uppercase tracking-widest text-text-tertiary mb-2">
                                    Sources used
                                </h4>
                                <ul class="flex flex-wrap gap-2">
                                    <li
                                        v-for="(src, i) in selected.context_sources"
                                        :key="i"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-border bg-white px-2.5 py-1 text-[10px] font-semibold text-text-secondary dark:bg-surface"
                                    >
                                        <FileText class="h-3 w-3 text-primary" />
                                        {{ src.name || src.document || src.doc_key || 'Document' }}
                                        <span v-if="src.version" class="text-[9px] font-mono text-text-tertiary">v{{ src.version }}</span>
                                        <span v-if="src.category" class="text-[9px] font-mono text-primary/70">{{ src.category }}</span>
                                        <span v-if="src.confidence || src.score" class="text-[9px] font-mono text-text-tertiary">
                                            {{ src.confidence || `${Math.round(src.score * 100)}%` }}
                                        </span>
                                    </li>
                                </ul>
                            </section>
                        </CardContent>
                        <CardFooter class="border-t border-border pt-4 flex-wrap gap-2 justify-end">
                            <Button variant="secondary" :disabled="isSubmitting" @click="openReject">
                                <X class="h-3.5 w-3.5" />
                                Reject
                            </Button>
                            <Button :disabled="isSubmitting" @click="approve">
                                <Check class="h-3.5 w-3.5" />
                                {{ isSubmitting ? 'Sending...' : 'Approve & Send' }}
                            </Button>
                        </CardFooter>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Reject modal -->
        <transition name="fade">
            <div v-if="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-950/40 backdrop-blur-sm" @click="showRejectModal = false" />
                <Card class="relative z-10 w-full max-w-md shadow-2xl">
                    <CardHeader>
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-danger/10 text-danger">
                                <AlertTriangle class="h-4 w-4" />
                            </div>
                            <div>
                                <CardTitle>Reject draft?</CardTitle>
                                <CardDescription>This will archive the AI draft. The customer will not receive a reply.</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-text-secondary mb-1.5">
                            Reason (optional)
                        </label>
                        <textarea
                            v-model="rejectionReason"
                            rows="3"
                            class="w-full rounded-xl border border-border bg-white text-xs text-text focus:border-primary focus:ring-primary/20 focus:ring-2 placeholder-text-tertiary p-3 dark:bg-surface"
                            placeholder="Briefly note why this draft was rejected..."
                        />
                    </CardContent>
                    <CardFooter class="justify-end gap-2">
                        <Button variant="secondary" @click="showRejectModal = false">Cancel</Button>
                        <Button variant="destructive" @click="confirmReject">Confirm Reject</Button>
                    </CardFooter>
                </Card>
            </div>
        </transition>


    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 200ms ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
