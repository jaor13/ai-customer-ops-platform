<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    BookOpen, Upload, FileText, X, Trash2, CheckCircle2,
    AlertCircle, Loader2, ScanLine,
} from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import PageEmpty from '@/Components/PageEmpty.vue';

const props = defineProps({
    documents: { type: Array, default: () => [] },
    options: { type: Object, default: () => ({ categories: [], departments: [] }) },
    maxUploadKb: { type: Number, default: 10240 },
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const maxMb = computed(() => Math.round(props.maxUploadKb / 1024));

const authorityLevels = [
    { value: 2.0, label: 'Official · 2.00', hint: 'Canonical / current source' },
    { value: 1.0, label: 'Normal · 1.00', hint: 'Standard reference (FAQ, SOP)' },
    { value: 0.5, label: 'Draft · 0.50', hint: 'Informal / unverified' },
];

const form = useForm({
    file: null,
    doc_key: '',
    category: 'general',
    department: '',
    authority_weight: 1.0,
    effective_from: '',
    effective_to: '',
});

const fileInput = ref(null);
const dragging = ref(false);

const setFile = (file) => {
    if (!file) return;
    form.file = file;
    form.clearErrors('file');
};

const onSelect = (e) => setFile(e.target.files?.[0]);
const onDrop = (e) => {
    dragging.value = false;
    setFile(e.dataTransfer.files?.[0]);
};
const clearFile = () => {
    form.file = null;
    if (fileInput.value) fileInput.value.value = '';
};

const prettySize = (bytes) => {
    if (!bytes) return '';
    const mb = bytes / (1024 * 1024);
    return mb >= 1 ? `${mb.toFixed(1)} MB` : `${Math.ceil(bytes / 1024)} KB`;
};

const submit = () => {
    form.post(route('knowledge-base.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            clearFile();
        },
    });
};

// --- delete ---
const deleting = ref(null);
const confirmDelete = (doc) => (deleting.value = doc);
const closeDelete = () => (deleting.value = null);
const performDelete = () => {
    if (!deleting.value) return;
    router.delete(route('knowledge-base.destroy', deleting.value.id), {
        preserveScroll: true,
        onFinish: closeDelete,
    });
};

// --- badge helpers ---
const categoryClass = 'text-blue-600 bg-blue-50 border-blue-100 dark:text-blue-400 dark:bg-blue-500/10 dark:border-blue-500/20';

const statusMeta = (status) => ({
    active: { label: 'Active', class: 'text-emerald-600 bg-emerald-50 border-emerald-100 dark:text-emerald-400 dark:bg-emerald-500/10 dark:border-emerald-500/20', icon: CheckCircle2 },
    processing: { label: 'Processing', class: 'text-amber-600 bg-amber-50 border-amber-100 dark:text-amber-400 dark:bg-amber-500/10 dark:border-amber-500/20', icon: Loader2 },
    superseded: { label: 'Superseded', class: 'text-slate-500 bg-slate-50 border-slate-200 dark:text-slate-400 dark:bg-slate-500/10 dark:border-slate-500/20', icon: null },
    failed: { label: 'Failed', class: 'text-red-600 bg-red-50 border-red-100 dark:text-red-400 dark:bg-red-500/10 dark:border-red-500/20', icon: AlertCircle },
    archived: { label: 'Archived', class: 'text-slate-500 bg-slate-50 border-slate-200 dark:text-slate-400 dark:bg-slate-500/10 dark:border-slate-500/20', icon: null },
}[status] ?? { label: status, class: 'text-slate-500 bg-slate-50 border-slate-200', icon: null });

const selectClass = 'w-full rounded-xl border-border bg-white text-text text-xs shadow-sm transition-all duration-200 focus:border-blue-500 focus:ring-blue-500/20 focus:ring-2 dark:bg-surface';
const inputClass = selectClass + ' placeholder-text-tertiary';
const capitalize = (s) => (s ? s.charAt(0).toUpperCase() + s.slice(1).replace(/_/g, ' ') : '');
</script>

<template>
    <Head title="Knowledge Base" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-extrabold font-display text-text tracking-tight sm:text-xl">Knowledge Base</h1>
        </template>

        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Flash -->
            <div
                v-if="flashSuccess"
                class="flex items-center gap-2 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-xs font-semibold text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-400"
            >
                <CheckCircle2 class="h-4 w-4 shrink-0" />
                {{ flashSuccess }}
            </div>
            <div
                v-if="flashError"
                class="flex items-center gap-2 rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-xs font-semibold text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-400"
            >
                <AlertCircle class="h-4 w-4 shrink-0" />
                {{ flashError }}
            </div>

            <!-- Upload card -->
            <Card>
                <CardHeader>
                    <CardTitle>Upload Document</CardTitle>
                    <CardDescription>
                        Files are stored, text-extracted (with OCR fallback for scanned PDFs), then chunked,
                        embedded, and indexed in Qdrant. Metadata drives authority ranking and active-version retrieval.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form class="space-y-5" @submit.prevent="submit">
                        <!-- Dropzone -->
                        <div>
                            <div
                                class="rounded-xl border border-dashed p-8 flex flex-col items-center justify-center text-center gap-3 transition-colors"
                                :class="dragging ? 'border-blue-500 bg-blue-50/60 dark:bg-blue-500/10' : 'border-border bg-surface-hover/40'"
                                role="button"
                                tabindex="0"
                                aria-label="Upload a document"
                                @click="fileInput?.click()"
                                @keydown.enter.prevent="fileInput?.click()"
                                @keydown.space.prevent="fileInput?.click()"
                                @dragover.prevent="dragging = true"
                                @dragleave.prevent="dragging = false"
                                @drop.prevent="onDrop"
                            >
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                                    <Upload class="h-5 w-5" />
                                </div>
                                <div v-if="!form.file">
                                    <p class="text-xs font-bold text-text">Drop a file or click to browse</p>
                                    <p class="text-[10px] text-text-tertiary mt-1">Supports PDF, DOCX, TXT, MD — max {{ maxMb }} MB</p>
                                </div>
                                <div v-else class="flex items-center gap-2 rounded-full border border-border bg-white px-3 py-1.5 dark:bg-surface">
                                    <FileText class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400" />
                                    <span class="text-xs font-bold text-text">{{ form.file.name }}</span>
                                    <span class="text-[10px] text-text-tertiary">{{ prettySize(form.file.size) }}</span>
                                    <button type="button" class="text-text-tertiary hover:text-danger" aria-label="Remove file" @click.stop="clearFile">
                                        <X class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                                <input
                                    ref="fileInput"
                                    type="file"
                                    class="sr-only"
                                    accept=".pdf,.txt,.md,.docx"
                                    @change="onSelect"
                                />
                            </div>
                            <InputError class="mt-1.5" :message="form.errors.file" />
                            <div v-if="form.progress" class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-surface-hover">
                                <div class="h-full rounded-full bg-blue-600 transition-all" :style="{ width: form.progress.percentage + '%' }" />
                            </div>
                        </div>

                        <!-- Metadata grid -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <InputLabel for="category" value="Category" />
                                <select id="category" v-model="form.category" :class="selectClass" class="mt-1.5">
                                    <option v-for="c in options.categories" :key="c" :value="c">{{ capitalize(c) }}</option>
                                </select>
                                <InputError class="mt-1.5" :message="form.errors.category" />
                            </div>

                            <div>
                                <InputLabel for="department" value="Department" />
                                <select id="department" v-model="form.department" :class="selectClass" class="mt-1.5">
                                    <option value="">— None —</option>
                                    <option v-for="d in options.departments" :key="d" :value="d">{{ capitalize(d) }}</option>
                                </select>
                                <InputError class="mt-1.5" :message="form.errors.department" />
                            </div>

                            <div>
                                <InputLabel for="authority_weight" value="Authority" />
                                <select id="authority_weight" v-model.number="form.authority_weight" :class="selectClass" class="mt-1.5">
                                    <option v-for="a in authorityLevels" :key="a.value" :value="a.value">{{ a.label }}</option>
                                </select>
                                <InputError class="mt-1.5" :message="form.errors.authority_weight" />
                            </div>

                            <div>
                                <InputLabel for="doc_key" value="Document Key (optional)" />
                                <input
                                    id="doc_key"
                                    v-model="form.doc_key"
                                    type="text"
                                    placeholder="e.g. pricing — derived from filename if blank"
                                    :class="inputClass"
                                    class="mt-1.5"
                                />
                                <p class="mt-1 text-[10px] text-text-tertiary">Same key across versions — re-uploading supersedes the old one.</p>
                                <InputError class="mt-1.5" :message="form.errors.doc_key" />
                            </div>

                            <div>
                                <InputLabel for="effective_from" value="Effective From (optional)" />
                                <input id="effective_from" v-model="form.effective_from" type="date" :class="inputClass" class="mt-1.5" />
                                <InputError class="mt-1.5" :message="form.errors.effective_from" />
                            </div>

                            <div>
                                <InputLabel for="effective_to" value="Effective To (optional)" />
                                <input id="effective_to" v-model="form.effective_to" type="date" :class="inputClass" class="mt-1.5" />
                                <InputError class="mt-1.5" :message="form.errors.effective_to" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-1">
                            <p class="mr-auto text-[10px] text-text-tertiary">
                                Higher authority sources win ties at query time; superseded versions stop surfacing automatically.
                            </p>
                            <Button type="submit" :disabled="form.processing || !form.file">
                                <Loader2 v-if="form.processing" class="h-3.5 w-3.5 animate-spin" />
                                <Upload v-else class="h-3.5 w-3.5" />
                                {{ form.processing ? 'Uploading…' : 'Upload & Index' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Documents list -->
            <Card>
                <CardHeader>
                    <CardTitle>Indexed Documents</CardTitle>
                    <CardDescription>{{ documents.length }} {{ documents.length === 1 ? 'document' : 'documents' }} in the knowledge base</CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <PageEmpty
                        v-if="documents.length === 0"
                        title="No documents yet"
                        description="Upload your first document to start grounding AI replies in your knowledge base."
                    >
                        <template #icon><BookOpen class="h-7 w-7" /></template>
                    </PageEmpty>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Document</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Category</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Dept</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Authority</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Ver</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Status</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Chunks</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Uploaded</th>
                                    <th class="px-5 py-3 text-right text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="doc in documents" :key="doc.id" class="hover:bg-surface-hover transition" :class="{ 'opacity-60': !doc.is_active }">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2">
                                            <FileText class="h-3.5 w-3.5 shrink-0 text-blue-600 dark:text-blue-400" />
                                            <div class="min-w-0">
                                                <p class="truncate font-bold text-text">{{ doc.original_name || doc.doc_key }}</p>
                                                <p class="flex items-center gap-1 text-[10px] text-text-tertiary">
                                                    <span class="font-mono">{{ doc.doc_key }}</span>
                                                    <span class="uppercase">· {{ doc.source_type }}</span>
                                                    <ScanLine v-if="doc.ocr_used" class="h-3 w-3 text-violet-500" title="OCR applied" />
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="text-[9px] font-mono font-bold px-1.5 py-0.5 rounded-md border" :class="categoryClass">{{ doc.category }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-text-secondary">{{ capitalize(doc.department) || '—' }}</td>
                                    <td class="px-5 py-3 font-mono text-[10px] text-text-secondary">{{ doc.authority_weight.toFixed(2) }}</td>
                                    <td class="px-5 py-3 font-mono text-[10px] text-text-tertiary">v{{ doc.version }}</td>
                                    <td class="px-5 py-3">
                                        <span
                                            class="inline-flex items-center gap-1 text-[9px] font-mono font-bold px-1.5 py-0.5 rounded-md border"
                                            :class="statusMeta(doc.status).class"
                                            :title="doc.ingest_error || ''"
                                        >
                                            <component
                                                :is="statusMeta(doc.status).icon"
                                                v-if="statusMeta(doc.status).icon"
                                                class="h-3 w-3"
                                                :class="{ 'animate-spin': doc.status === 'processing' }"
                                            />
                                            {{ statusMeta(doc.status).label }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-text-secondary">{{ doc.chunk_count ?? 0 }}</td>
                                    <td class="px-5 py-3 text-text-tertiary">{{ doc.created_at }}</td>
                                    <td class="px-5 py-3 text-right">
                                        <button
                                            type="button"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-text-tertiary transition-colors hover:bg-red-50 hover:text-danger focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500/40 dark:hover:bg-red-500/10"
                                            :aria-label="`Delete ${doc.original_name || doc.doc_key}`"
                                            @click="confirmDelete(doc)"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Delete confirmation -->
        <Modal :show="deleting !== null" @close="closeDelete">
            <div class="p-6">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-50 text-danger dark:bg-red-500/10">
                        <AlertCircle class="h-5 w-5" />
                    </div>
                    <div>
                        <h2 class="text-sm font-extrabold font-display text-text">Delete document?</h2>
                        <p class="mt-1 text-xs text-text-secondary">
                            <span class="font-semibold text-text">{{ deleting?.original_name || deleting?.doc_key }}</span>
                            will be removed from storage and the index. This can't be undone.
                        </p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="closeDelete">Cancel</SecondaryButton>
                    <DangerButton @click="performDelete">Delete</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
