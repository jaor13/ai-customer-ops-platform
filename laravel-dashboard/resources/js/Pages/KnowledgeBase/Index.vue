<script setup>
import { Head } from '@inertiajs/vue3';
import { BookOpen, Upload, FileText } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import PageEmpty from '@/Components/PageEmpty.vue';

defineProps({
    documents: { type: Array, default: () => [] },
});

const departmentVariant = (dep) => {
    const map = { sales: 'default', support: 'success', billing: 'warning', general: 'secondary' };
    return map[dep] || 'secondary';
};
</script>

<template>
    <Head title="Knowledge Base" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-extrabold font-display text-text tracking-tight sm:text-xl">Knowledge Base</h1>
        </template>

        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Upload card -->
            <Card>
                <CardHeader>
                    <CardTitle>Upload Document</CardTitle>
                    <CardDescription>Upload PDFs, DOCX, or TXT files. They will be chunked, embedded, and added to Qdrant.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="rounded-xl border border-dashed border-border bg-surface-hover/40 p-8 flex flex-col items-center justify-center text-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                            <Upload class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-text">Drop a file or click to browse</p>
                            <p class="text-[10px] text-text-tertiary mt-1">Supports PDF, DOCX, TXT — max 10 MB</p>
                        </div>
                        <Button size="sm">Select File</Button>
                    </div>
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
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Filename</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Department</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Version</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Chunks</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Uploaded</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="doc in documents" :key="doc.id" class="hover:bg-surface-hover transition">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2">
                                            <FileText class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400" />
                                            <span class="font-bold text-text">{{ doc.original_name || doc.filename }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3"><Badge :variant="departmentVariant(doc.department)">{{ doc.department || '—' }}</Badge></td>
                                    <td class="px-5 py-3 font-mono text-[10px] text-text-tertiary">v{{ doc.version }}</td>
                                    <td class="px-5 py-3 text-text-secondary">{{ doc.chunk_count || 0 }}</td>
                                    <td class="px-5 py-3 text-text-tertiary">{{ doc.created_at }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
