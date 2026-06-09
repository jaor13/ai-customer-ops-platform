<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
});

// Interactive Outbox Simulator
const demoDrafts = ref([
    {
        recipient: 'sarah.jenkins@vaporscale.com',
        subject: 'API Limits & Custom Webhooks',
        score: 'HOT (97%)',
        scoreColor: 'text-amber-500 bg-amber-500/10 border-amber-500/20',
        message: 'Hello, does your platform support custom webhooks? We need to route about 10k emails a day.',
        reply: "Hi Sarah,\n\nYes, we fully support custom webhooks. Our platform integrates with n8n and Laravel, allowing you to route 10k+ emails daily. Would you like to schedule a quick demo this week?\n\nBest,\nAI Ops Team",
        sourceDoc: 'API_Specs_v2.pdf',
        confidence: '98% confidence'
    },
    {
        recipient: 'marcus.t@solosaas.io',
        subject: 'Pre-Seed Startup Discount',
        score: 'WARM (62%)',
        scoreColor: 'text-blue-600 bg-blue-50 border-blue-100 dark:text-blue-400 dark:bg-blue-500/10',
        message: 'Do you guys have any discounts for early stage pre-seed startups?',
        reply: "Hi Marcus,\n\nThanks for reaching out! We offer a pre-seed startup tier with 40% off our annual plans for the first year. Here is the link to apply...\n\nWarm regards,\nAI Ops Team",
        sourceDoc: 'Pricing_Sheet.pdf',
        confidence: '91% confidence'
    },
    {
        recipient: 'dave.miller@nexustech.org',
        subject: 'Database Sync Timeout error',
        message: 'Getting sync timeout errors at midnight during heavy load. Any recommendations?',
        score: 'HOT (85%)',
        scoreColor: 'text-amber-500 bg-amber-500/10 border-amber-500/20',
        reply: "Hi Dave,\n\nI looked up our knowledge base for sync timeouts. It appears to be related to the midnight cron schedule. Please check the DB connections and retry using our Qdrant sync script. Let us know if this works!\n\nBest,\nAI Ops Support",
        sourceDoc: 'DB_Tuning_Guide.pdf',
        confidence: '95% confidence'
    }
]);

const activeIndex = ref(0);
const isProcessing = ref(false);
const approvedLogCount = ref(1524);
const showSentBanner = ref(false);

const approveResponse = () => {
    if (isProcessing.value) return;
    isProcessing.value = true;
    
    setTimeout(() => {
        isProcessing.value = false;
        showSentBanner.value = true;
        approvedLogCount.value++;
        
        setTimeout(() => {
            showSentBanner.value = false;
            activeIndex.value = (activeIndex.value + 1) % demoDrafts.value.length;
        }, 1200);
    }, 1000);
};

// Bento Grid Toggle States
const bentoLiveApprovalGate = ref(true);

// Lead Capture Form
const leadForm = ref({
    name: '',
    email: '',
    company: '',
    phone: '',
    message: '',
});
const leadFormSubmitting = ref(false);
const leadFormSuccess = ref(false);
const leadFormError = ref('');

const submitLeadForm = async () => {
    leadFormSubmitting.value = true;
    leadFormSuccess.value = false;
    leadFormError.value = '';

    try {
        const response = await fetch('https://n8n.ai-ops.jaor13.app/webhook/lead', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                name: leadForm.value.name,
                email: leadForm.value.email,
                company: leadForm.value.company,
                phone: leadForm.value.phone,
                message: leadForm.value.message,
                source: 'website',
            }),
        });

        if (response.ok) {
            leadFormSuccess.value = true;
            leadForm.value = { name: '', email: '', company: '', phone: '', message: '' };
        } else {
            leadFormError.value = 'Something went wrong. Please try again.';
        }
    } catch (err) {
        leadFormError.value = 'Unable to reach the server. Please try again later.';
    } finally {
        leadFormSubmitting.value = false;
    }
};
</script>

<template>
    <Head title="AI Customer Ops — Intelligent Operations Platform" />

    <!-- Main Container in Warm Cream Light Theme -->
    <div class="min-h-screen bg-[#FAF8F5] text-slate-900 font-sans selection:bg-blue-500 selection:text-white overflow-x-hidden">
        
        <!-- Elegant Dotted Grid Background -->
        <div class="absolute inset-0 bg-dot-pattern opacity-[0.7] pointer-events-none" style="-webkit-mask-image: radial-gradient(circle at top, black 50%, transparent 100%); mask-image: radial-gradient(circle at top, black 50%, transparent 100%);"></div>

        <!-- Float Accent Lights (Static to prevent distraction) -->
        <div class="absolute top-[-10%] left-[10%] h-[500px] w-[500px] rounded-full bg-blue-100/30 blur-[130px] pointer-events-none"></div>
        <div class="absolute top-[20%] right-[10%] h-[400px] w-[400px] rounded-full bg-indigo-100/20 blur-[100px] pointer-events-none"></div>

        <!-- Header / Navigation -->
        <nav class="fixed top-4 left-1/2 -translate-x-1/2 z-50 w-full max-w-5xl px-4">
            <div class="glassmorphism rounded-full border border-slate-200/50 bg-white/70 px-6 py-2.5 shadow-md flex items-center justify-between">
                <!-- Logo -->
                <Link href="/" class="flex items-center gap-2.5 group">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 shadow-md shadow-blue-500/10 group-hover:scale-105 transition-transform duration-300">
                        <svg class="h-4.5 w-4.5 text-white" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <polygon points="50,12 88,34 88,78 50,100 12,78 12,34" stroke="currentColor" stroke-width="8" stroke-linejoin="round"/>
                            <path d="M50,12 L50,56 L88,78 M50,56 L12,78" stroke="currentColor" stroke-width="6" stroke-linejoin="round"/>
                            <circle cx="50" cy="56" r="10" fill="currentColor"/>
                        </svg>
                    </div>
                    <span class="text-xs font-bold font-display uppercase tracking-widest text-slate-900 group-hover:text-blue-600 transition-colors">AI Ops</span>
                </Link>

                <!-- Nav Links -->
                <div class="hidden md:flex items-center gap-8 text-[11px] font-bold uppercase tracking-widest text-slate-500">
                    <a href="#how-it-works" class="hover:text-slate-900 transition-colors">How it works</a>
                    <a href="#features" class="hover:text-slate-900 transition-colors">Features</a>
                    <a href="#contact" class="hover:text-slate-900 transition-colors">Contact</a>
                    <a href="https://github.com/jaor13/ai-customer-ops-platform" target="_blank" class="hover:text-slate-900 transition-colors flex items-center gap-1">
                        GITHUB
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>

                <!-- Auth Navigation -->
                <div v-if="canLogin" class="flex items-center gap-5">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="rounded-full bg-blue-600 px-5.5 py-1.5 text-xs font-bold text-white shadow-md shadow-blue-500/10 hover:shadow-blue-500/20 hover:bg-blue-700 transition-all duration-300"
                    >
                        Go to Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="text-[11px] font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900 transition-colors whitespace-nowrap"
                        >
                            Sign In
                        </Link>
                        <Link
                            :href="route('login')"
                            class="rounded-full bg-blue-600 hover:bg-blue-700 px-5 py-2 text-xs font-bold text-white shadow-md shadow-blue-500/10 hover:shadow-blue-500/25 transition-all duration-300 whitespace-nowrap"
                        >
                            Get Started
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative pt-24 pb-16 lg:pt-36 lg:pb-20">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                    
                    <!-- Left Column: Copy -->
                    <div class="lg:col-span-7 space-y-6 text-center lg:text-left relative">
                        <!-- Abstract Background Pattern Grid -->
                        <div class="absolute inset-0 -top-10 left-1/2 lg:left-0 -translate-x-1/2 lg:translate-x-0 w-[500px] h-[300px] opacity-[0.15] pointer-events-none select-none z-[-1] hidden md:block">
                            <svg class="w-full h-full text-blue-500" viewBox="0 0 600 300" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M150 50 L300 150 L450 50 M300 150 L300 280" stroke="currentColor" stroke-dasharray="3 3" stroke-width="1.5" />
                                <circle cx="150" cy="50" r="4" fill="currentColor" />
                                <circle cx="300" cy="150" r="6" fill="currentColor" />
                                <circle cx="450" cy="50" r="4" fill="currentColor" />
                                <path d="M100 150h400M200 220h200" stroke="currentColor" stroke-opacity="0.3" stroke-width="1" stroke-dasharray="2 4" />
                            </svg>
                        </div>

                        <!-- Tech Badge -->
                        <div class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 border border-blue-100 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-blue-600">
                            <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                            INTELLIGENT INTEGRATION
                        </div>

                        <!-- Clean Geometric Headline -->
                        <h1 class="text-3xl font-extrabold font-display tracking-tight text-slate-900 sm:text-5xl lg:text-6xl leading-[1.08]">
                            Go live with AI customer operations <span class="text-blue-600 font-display">this quarter</span>
                        </h1>

                        <!-- Editorial Subtitle -->
                        <p class="text-xs sm:text-sm md:text-base text-slate-500 leading-relaxed max-w-xl mx-auto lg:mx-0">
                            Connect visual workflows, score leads automatically, and construct grounded database answers—retaining full human-in-the-loop validation for every outbound response.
                        </p>

                        <!-- CTAs -->
                        <div class="flex items-center justify-center lg:justify-start gap-3 pt-3">
                            <Link
                                v-if="canLogin && !$page.props.auth.user"
                                :href="route('login')"
                                class="rounded-full bg-blue-600 hover:bg-blue-700 px-6 py-2.5 text-xs font-bold text-white shadow-lg shadow-blue-600/10 hover:shadow-blue-600/25 transition-all duration-300"
                            >
                                Get started today
                            </Link>
                            <a
                                href="#how-it-works"
                                class="rounded-full border border-slate-200 bg-white hover:bg-slate-50 px-6 py-2.5 text-xs font-bold text-slate-600 transition-all duration-300"
                            >
                                Learn more
                            </a>
                        </div>
                    </div>

                    <!-- Right Column: Premium AI Flow Image Mockup -->
                    <div class="lg:col-span-5 flex justify-center relative select-none">
                        <!-- Background Grid pattern overlay -->
                        <div class="absolute inset-0 -top-4 -left-4 bg-grid-pattern opacity-40 rounded-3xl border border-slate-200/50 pointer-events-none z-0"></div>

                        <!-- Glassmorphic Image Card Container -->
                        <div class="relative z-10 p-3 rounded-3xl bg-white/70 border border-slate-200/50 shadow-2xl animate-float max-w-sm overflow-hidden hover:scale-105 transition-all duration-500">
                            <img src="/images/ai_flow_mockup.png" alt="AI Operations Flow diagram" class="rounded-2xl border border-slate-200/40 w-full object-cover shadow-sm bg-white" />
                            
                            <!-- Floating mini-badge over image -->
                            <div class="absolute bottom-6 right-6 glassmorphism rounded-xl border border-slate-200/50 p-2 shadow-lg flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="text-[9px] font-bold font-mono text-slate-900">PIPELINE ACTIVE</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Grayscale Integration Cloud (Directly below hero) -->
                <div class="mt-16 border-t border-slate-200/50 pt-8 max-w-4xl mx-auto">
                    <p class="text-center text-[9px] font-mono font-bold uppercase tracking-widest text-slate-400">OUT-OF-THE-BOX INTEGRATION CHANNELS</p>
                    <div class="mt-6 flex flex-wrap items-center justify-center gap-x-12 gap-y-6 opacity-35 hover:opacity-55 transition-opacity duration-300">
                        <span class="text-xs font-bold font-mono tracking-widest text-slate-900">SALESFORCE</span>
                        <span class="text-xs font-bold font-mono tracking-widest text-slate-900">HUBSPOT</span>
                        <span class="text-xs font-bold font-mono tracking-widest text-slate-900">ZENDESK</span>
                        <span class="text-xs font-bold font-mono tracking-widest text-slate-900">SLACK</span>
                        <span class="text-xs font-bold font-mono tracking-widest text-slate-900">GMAIL API</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- "How it works" Dual-Panel Section -->
        <section id="how-it-works" class="py-16 md:py-24 border-t border-slate-200/40 relative">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-start">
                    
                    <!-- Left Column: Step timeline -->
                    <div class="lg:col-span-5 space-y-6">
                        <div class="space-y-2">
                            <span class="text-[10px] font-mono font-bold tracking-widest text-blue-600 uppercase">PROCESSING STORYBOARD</span>
                            <h2 class="text-3xl font-extrabold font-display text-slate-900 tracking-tight">How it works.</h2>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Deploy custom workflows from your platform with ease by routing incoming inquiries directly through our validation APIs.
                            </p>
                        </div>

                        <!-- Vertical Stack of Storyboard Cards -->
                        <div class="space-y-4 relative">
                            <!-- Vertical track line behind cards -->
                            <div class="absolute left-8 top-8 bottom-8 w-0.5 border-l border-dashed border-slate-200 pointer-events-none"></div>

                            <!-- Card 1 -->
                            <div class="relative group bg-white/80 border border-slate-200/50 shadow-sm p-4 rounded-2xl flex items-start gap-4 transition-all duration-300 hover:shadow-md hover:border-blue-500/20 hover:bg-white">
                                <!-- Step SVG Icon -->
                                <div class="relative z-10 shrink-0">
                                    <svg class="h-10 w-10 text-blue-600" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect width="48" height="48" rx="10" fill="#eff6ff" />
                                        <path d="M12 16h24v18H12z" stroke="#2563eb" stroke-width="1.5" stroke-linejoin="round" />
                                        <path d="M12 18l12 9 12-9" stroke="#2563eb" stroke-width="1.5" stroke-linejoin="round" />
                                        <circle cx="24" cy="30" r="3" fill="#2563eb" />
                                        <path d="M6 24h6M36 24h6" stroke="#93c5fd" stroke-width="1.5" stroke-dasharray="2 2" />
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[9px] font-mono font-extrabold tracking-widest text-blue-600 bg-blue-50 border border-blue-100 px-1.5 py-0.5 rounded">01 / INTAKE</span>
                                    </div>
                                    <h4 class="text-xs font-bold text-slate-950">Issue credentials & connect intake</h4>
                                    <p class="text-[11px] text-slate-500 leading-relaxed">
                                        Link incoming customer emails, webhooks, or ticketing APIs straight into the queue.
                                    </p>
                                </div>
                            </div>

                            <!-- Card 2 -->
                            <div class="relative group bg-white/80 border border-slate-200/50 shadow-sm p-4 rounded-2xl flex items-start gap-4 transition-all duration-300 hover:shadow-md hover:border-indigo-500/20 hover:bg-white">
                                <div class="relative z-10 shrink-0">
                                    <svg class="h-10 w-10 text-indigo-600" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect width="48" height="48" rx="10" fill="#f5f3ff" />
                                        <path d="M14 12h20v24H14z" stroke="#6366f1" stroke-width="1.5" />
                                        <path d="M18 18h12M18 24h12" stroke="#818cf8" stroke-width="1.5" />
                                        <line x1="10" y1="28" x2="38" y2="28" stroke="#4f46e5" stroke-width="2" stroke-linecap="round" />
                                        <rect x="28" y="28" width="12" height="6" rx="2" fill="#818cf8" />
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[9px] font-mono font-extrabold tracking-widest text-indigo-600 bg-indigo-50 border border-indigo-100 px-1.5 py-0.5 rounded">02 / TRIAGE</span>
                                    </div>
                                    <h4 class="text-xs font-bold text-slate-950">AI enrichment & priority triaging</h4>
                                    <p class="text-[11px] text-slate-500 leading-relaxed">
                                        GPT-4o checks demographics, analyzes message intent, and applies priority tags.
                                    </p>
                                </div>
                            </div>

                            <!-- Card 3 -->
                            <div class="relative group bg-white/80 border border-slate-200/50 shadow-sm p-4 rounded-2xl flex items-start gap-4 transition-all duration-300 hover:shadow-md hover:border-violet-500/20 hover:bg-white">
                                <div class="relative z-10 shrink-0">
                                    <svg class="h-10 w-10 text-violet-600" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect width="48" height="48" rx="10" fill="#faf5ff" />
                                        <circle cx="24" cy="24" r="4" fill="#8b5cf6" />
                                        <circle cx="14" cy="14" r="3" stroke="#a78bfa" stroke-width="1.5" />
                                        <circle cx="34" cy="14" r="3" stroke="#a78bfa" stroke-width="1.5" />
                                        <circle cx="14" cy="34" r="3" stroke="#a78bfa" stroke-width="1.5" />
                                        <circle cx="34" cy="34" r="3" stroke="#a78bfa" stroke-width="1.5" />
                                        <line x1="17" y1="17" x2="21" y2="21" stroke="#c084fc" stroke-width="1.5" stroke-dasharray="2 1" />
                                        <line x1="31" y1="17" x2="27" y2="21" stroke="#c084fc" stroke-width="1.5" stroke-dasharray="2 1" />
                                        <line x1="17" y1="31" x2="21" y2="27" stroke="#c084fc" stroke-width="1.5" stroke-dasharray="2 1" />
                                        <line x1="31" y1="31" x2="27" y2="27" stroke="#c084fc" stroke-width="1.5" stroke-dasharray="2 1" />
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[9px] font-mono font-extrabold tracking-widest text-violet-600 bg-violet-50 border border-violet-100 px-1.5 py-0.5 rounded">03 / RETRIEVE</span>
                                    </div>
                                    <h4 class="text-xs font-bold text-slate-950">Knowledge-base RAG synthesis</h4>
                                    <p class="text-[11px] text-slate-500 leading-relaxed">
                                        Search indices in Qdrant fetch document data to build factual outbound response drafts.
                                    </p>
                                </div>
                            </div>

                            <!-- Card 4 -->
                            <div class="relative group bg-white/80 border border-slate-200/50 shadow-sm p-4 rounded-2xl flex items-start gap-4 transition-all duration-300 hover:shadow-md hover:border-emerald-500/20 hover:bg-white">
                                <div class="relative z-10 shrink-0">
                                    <svg class="h-10 w-10 text-emerald-600" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect width="48" height="48" rx="10" fill="#ecfdf5" />
                                        <rect x="16" y="10" width="16" height="28" rx="3" stroke="#10b981" stroke-width="1.5" />
                                        <circle cx="24" cy="24" r="7" fill="#34d399" fill-opacity="0.2" />
                                        <path d="M20 24l3 3 6-6" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[9px] font-mono font-extrabold tracking-widest text-emerald-600 bg-emerald-50 border border-emerald-100 px-1.5 py-0.5 rounded">04 / DISPATCH</span>
                                    </div>
                                    <h4 class="text-xs font-bold text-slate-950">Human approval dispatch</h4>
                                    <p class="text-[11px] text-slate-500 leading-relaxed">
                                        Your support staff reviews the pre-composed response, edits if necessary, and dispatches it.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Interactive Browser Dashboard Mockup (Beige/Grid backdrop) -->
                    <div class="lg:col-span-7 relative animate-float">
                        <!-- Tiny grid layout behind mockup card -->
                        <div class="absolute inset-0 -top-6 -left-6 bg-grid-pattern opacity-60 rounded-2xl border border-slate-200 pointer-events-none"></div>

                        <!-- Interactive Form Card Mockup -->
                        <div class="relative z-10 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xl select-none">
                            
                            <!-- Card Header -->
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                                <span class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                    <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                                    Pending Approval
                                </span>
                                <div class="flex gap-2">
                                    <span v-for="(draft, idx) in demoDrafts" :key="idx" 
                                        @click="activeIndex = idx"
                                        :class="[activeIndex === idx ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200', 'px-2 py-0.5 rounded text-[10px] font-mono cursor-pointer transition']"
                                    >
                                        Lead {{ idx + 1 }}
                                    </span>
                                </div>
                            </div>

                            <!-- Success Banner -->
                            <div v-if="showSentBanner" class="mb-4 bg-emerald-50 border border-emerald-100 text-emerald-700 p-2.5 rounded-xl text-center text-xs font-medium animate-bounce flex items-center justify-center gap-2">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                Email dispatched via visual API hook!
                            </div>

                            <!-- Form Details -->
                            <div class="space-y-4">
                                <!-- Recipient -->
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wide block">Recipient</label>
                                    <div class="mt-1 text-xs font-semibold text-slate-900 border-b border-slate-100 pb-1.5">
                                        {{ demoDrafts[activeIndex].recipient }}
                                    </div>
                                </div>

                                <!-- Lead Evaluation Score -->
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wide block">Triage Classification</label>
                                    <div class="mt-1 flex items-center justify-between">
                                        <span class="text-xs font-bold text-slate-800">{{ demoDrafts[activeIndex].subject }}</span>
                                        <span :class="['text-[9px] font-mono font-bold px-1.5 py-0.5 rounded-md border', demoDrafts[activeIndex].scoreColor]">
                                            Score: {{ demoDrafts[activeIndex].score }}
                                        </span>
                                    </div>
                                </div>

                                <!-- AI Draft Reply -->
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wide block">Drafted Response</label>
                                    <div class="mt-1.5 p-3 rounded-xl bg-slate-50 border border-slate-200/60 text-xs text-slate-700 leading-relaxed font-mono whitespace-pre-line">
                                        {{ demoDrafts[activeIndex].reply }}
                                    </div>
                                </div>
                            </div>

                            <!-- Card Footer Actions -->
                            <div class="mt-5 border-t border-slate-100 pt-4 flex items-center justify-between">
                                <span class="text-[9px] font-mono text-slate-400">Approved Logs: {{ approvedLogCount }}</span>
                                <div class="flex gap-2.5">
                                    <button 
                                        @click="activeIndex = (activeIndex + 1) % demoDrafts.length"
                                        class="px-3.5 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-500 hover:bg-slate-50 transition"
                                    >
                                        Skip
                                    </button>
                                    <button 
                                        @click="approveResponse"
                                        :disabled="isProcessing"
                                        class="px-4 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-xs font-semibold text-white flex items-center gap-1.5 transition active:scale-95 disabled:opacity-50"
                                    >
                                        <svg v-if="isProcessing" class="h-3 w-3 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>{{ isProcessing ? 'Sending...' : 'Approve & Send' }}</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Support Floating Widget overlay in the corner -->
                            <div class="absolute -bottom-8 -right-6 glassmorphism rounded-xl border border-slate-200/50 p-2.5 shadow-lg max-w-[200px] flex items-start gap-2 animate-float">
                                <div class="h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-xs shrink-0 font-bold font-mono">Q</div>
                                <div>
                                    <div class="text-[9px] font-bold text-slate-900 uppercase">Vector Grounding</div>
                                    <div class="text-[8px] text-slate-500 leading-tight mt-0.5">Matched {{ demoDrafts[activeIndex].sourceDoc }} ({{ demoDrafts[activeIndex].confidence }})</div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- DOSS-style Dark Wireframe Grid Section (Masters of perspective) -->
        <section class="py-20 bg-slate-950 text-white relative border-t border-b border-slate-900 overflow-hidden">
            <!-- 3D Perspective Grid Background (DOSS style) -->
            <div class="absolute inset-0 w-full h-full perspective-grid opacity-30 pointer-events-none">
                <div class="w-full h-[200%] perspective-grid-inner"></div>
            </div>

            <!-- Glowing light orbs -->
            <div class="absolute top-[30%] left-[25%] h-[350px] w-[350px] rounded-full bg-blue-500/5 blur-[90px] pointer-events-none"></div>

            <div class="mx-auto max-w-5xl px-4 sm:px-6 relative z-10 flex flex-col items-center text-center">
                <div class="max-w-2xl">
                    <span class="text-[10px] font-mono font-bold tracking-widest text-blue-500 uppercase block mb-3">VECTOR KNOWLEDGE SYNC</span>
                    <h2 class="text-3xl font-extrabold font-display text-white sm:text-4xl tracking-tight leading-tight">
                        RAG Grounding pipeline evolved
                    </h2>
                    <p class="mt-4 text-xs md:text-sm text-slate-400 leading-relaxed">
                        Watch how files uploaded to your admin console automatically break into chunked vector embeddings, storing directly in Qdrant database nodes.
                    </p>
                </div>

                <!-- Isometric block visualization using inline SVG with 3D Float Animations -->
                <div class="mt-12 w-full max-w-sm hover:scale-105 transition-transform duration-500">
                    <svg viewBox="0 0 400 370" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
                        <!-- Top Floating wireframe ball -->
                        <g class="animate-float-top">
                            <circle cx="200" cy="18" r="8" stroke="#3b82f6" stroke-width="1.2" stroke-dasharray="2 2" fill="none" />
                            <line x1="200" y1="26" x2="200" y2="45" stroke="#3b82f6" stroke-width="1" stroke-dasharray="3 3" />
                        </g>
                        
                        <!-- Top Isometric Box (Ingestion layer - Float Top) -->
                        <g class="animate-float-top">
                            <path d="M200 45 L320 85 L200 125 L80 85 Z" fill="#2563eb" fill-opacity="0.9" stroke="#3b82f6" stroke-width="1.5" />
                            <path d="M80 85 L80 110 L200 150 L200 125 Z" fill="#1d4ed8" fill-opacity="0.95" stroke="#3b82f6" stroke-width="1.5" />
                            <path d="M200 125 L200 150 L320 110 L320 85 Z" fill="#1e40af" fill-opacity="0.95" stroke="#3b82f6" stroke-width="1.5" />
                            <text x="200" y="90" fill="#ffffff" font-size="10" font-family="monospace" text-anchor="middle" font-weight="bold" letter-spacing="1">QDRANT RAG</text>
                        </g>
                        
                        <!-- Middle Isometric Box (Triage layer - Float Mid) -->
                        <g class="animate-float-mid">
                            <path d="M200 120 L320 160 L200 200 L80 160 Z" fill="#3b82f6" fill-opacity="0.8" stroke="#60a5fa" stroke-width="1.5" />
                            <path d="M80 160 L80 185 L200 225 L200 200 Z" fill="#2563eb" fill-opacity="0.85" stroke="#60a5fa" stroke-width="1.5" />
                            <path d="M200 200 L200 225 L320 185 L320 160 Z" fill="#1d4ed8" fill-opacity="0.85" stroke="#60a5fa" stroke-width="1.5" />
                            <text x="200" y="165" fill="#ffffff" font-size="10" font-family="monospace" text-anchor="middle" font-weight="bold" letter-spacing="1">CLASSIFICATION</text>
                        </g>

                        <!-- Bottom Isometric Box (Webhook layer - Float Bot) -->
                        <g class="animate-float-bot">
                            <path d="M200 195 L320 235 L200 275 L80 235 Z" fill="#60a5fa" fill-opacity="0.7" stroke="#93c5fd" stroke-width="1.5" />
                            <path d="M80 235 L80 260 L200 300 L200 275 Z" fill="#3b82f6" fill-opacity="0.75" stroke="#93c5fd" stroke-width="1.5" />
                            <path d="M200 275 L200 300 L320 260 L320 235 Z" fill="#2563eb" fill-opacity="0.75" stroke="#93c5fd" stroke-width="1.5" />
                            <text x="200" y="240" fill="#ffffff" font-size="10" font-family="monospace" text-anchor="middle" font-weight="bold" letter-spacing="1">WEBHOOK INTAKE</text>
                        </g>

                        <!-- Ground rings -->
                        <ellipse cx="200" cy="330" rx="140" ry="25" stroke="#2563eb" stroke-opacity="0.3" stroke-width="1" stroke-dasharray="4 4" />
                        <ellipse cx="200" cy="330" rx="100" ry="18" stroke="#3b82f6" stroke-opacity="0.4" stroke-width="1.2" />
                    </svg>
                </div>
            </div>
        </section>

        <!-- Bento Grid Features Section -->
        <section id="features" class="py-16 md:py-24 border-t border-slate-200/40 relative">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 relative z-10">
                <div class="text-center max-w-xl mx-auto mb-16">
                    <h2 class="text-3xl font-extrabold font-display text-slate-900 tracking-tight leading-tight">
                        Powering enterprise class operations
                    </h2>
                    <p class="mt-3 text-xs md:text-sm text-slate-500 leading-relaxed">
                        Unleashing speed and automation. AI does the background work while you retain authorization keys.
                    </p>
                </div>
 
                <!-- Bento Grid Layout -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Feature: Lead Scoring (Large column span) -->
                    <div class="md:col-span-2 group rounded-2xl border border-slate-200/60 bg-white hover:shadow-lg hover:border-blue-500/20 p-8 transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 mb-6 group-hover:scale-105 transition-transform duration-300">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2C6.5 6.5 4 10.5 4 14.5C4 18.5 7.5 22 12 22C16.5 22 20 18.5 20 14.5C20 10.5 17.5 6.5 12 2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    <path d="M12 8C9.5 10.5 8 13 8 15C8 17 9.8 18.5 12 18.5C14.2 18.5 16 17 16 15C16 13 14.5 10.5 12 8Z" fill="currentColor"/>
                                </svg>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">Prospect Intelligence</h3>
                            <p class="mt-2 text-xs text-slate-500 leading-relaxed max-w-md">
                                Score prospects automatically based on domain firmographic data and incoming email intent parameters. Filter out spam and route VIPs to senior executives immediately.
                            </p>
                        </div>
                        <div class="mt-8 space-y-2.5 border-t border-slate-100 pt-5">
                            <div class="flex items-center justify-between text-xs bg-slate-50 border border-slate-100/60 p-2.5 rounded-xl">
                                <span class="font-bold text-slate-800 flex items-center gap-1.5">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                    sarah.j@vaporscale.com
                                </span>
                                <span class="text-[10px] font-mono font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">Enterprise - 98 Score</span>
                            </div>
                            <div class="flex items-center justify-between text-xs bg-slate-50 border border-slate-100/60 p-2.5 rounded-xl">
                                <span class="font-bold text-slate-800 flex items-center gap-1.5">
                                    <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                    marcus.t@solosaas.io
                                </span>
                                <span class="text-[10px] font-mono font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100">Mid-Market - 82 Score</span>
                            </div>
                        </div>
                    </div>
 
                    <!-- Feature: Email Triage -->
                    <div class="group rounded-2xl border border-slate-200/60 bg-white hover:shadow-lg hover:border-blue-500/20 p-8 transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-600 mb-6 group-hover:scale-105 transition-transform duration-300">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="3" y="3" width="18" height="18" rx="4" stroke="currentColor" stroke-width="1.8" />
                                    <path d="M7 8h10M7 12h10M7 16h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                    <circle cx="17" cy="16" r="3" fill="currentColor" />
                                </svg>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">Email Triage Classification</h3>
                            <p class="mt-2 text-xs text-slate-500 leading-relaxed">
                                Classify incoming emails by category and priority. Automatically assign tags (Tech, Sales, Billing).
                            </p>
                        </div>
                        <div class="mt-6 space-y-2 pt-4 border-t border-slate-100">
                            <div class="text-[9px] font-mono font-bold text-slate-400 uppercase">Triage Output</div>
                            <div class="flex flex-wrap gap-2">
                                <span class="text-[10px] font-bold bg-amber-50 text-amber-700 px-2 py-1 rounded-xl border border-amber-100/70">Category: Technical</span>
                                <span class="text-[10px] font-bold bg-rose-50 text-rose-700 px-2 py-1 rounded-xl border border-rose-100/70">Priority: High</span>
                            </div>
                        </div>
                    </div>
 
                    <!-- Feature: RAG Knowledge Base -->
                    <div class="group rounded-2xl border border-slate-200/60 bg-white hover:shadow-lg hover:border-blue-500/20 p-8 transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600 mb-6 group-hover:scale-105 transition-transform duration-300">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z" stroke="currentColor" stroke-width="1.8" />
                                    <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.8" />
                                    <path d="M12 4v4M12 16v4M4 12h4M16 12h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                </svg>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">RAG Vector Retrieval</h3>
                            <p class="mt-2 text-xs text-slate-500 leading-relaxed">
                                Upload support manuals or database files to build response drafts based on accurate data.
                            </p>
                        </div>
                        <div class="mt-6 bg-slate-50 border border-slate-100 rounded-xl p-3 text-[10px] font-mono text-slate-500 space-y-1">
                            <div class="flex items-center justify-between">
                                <span>Vector database match</span>
                                <span class="text-emerald-600 font-bold">94% Confidence</span>
                            </div>
                            <div class="w-full bg-slate-200 h-1 rounded-full overflow-hidden">
                                <div class="bg-emerald-500 h-full w-[94%]"></div>
                            </div>
                        </div>
                    </div>
 
                    <!-- Feature: Human Approval (Large column span) -->
                    <div class="md:col-span-2 group rounded-2xl border border-slate-200/60 bg-white hover:shadow-lg hover:border-blue-500/20 p-8 transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 mb-6 group-hover:scale-105 transition-transform duration-300">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">Human-in-the-Loop Safeguard</h3>
                            <p class="mt-2 text-xs text-slate-500 leading-relaxed max-w-md">
                                Keep an absolute check on outbound email dispatches. Toggle the interactive status switch below to see it function live.
                            </p>
                        </div>
                        <div class="mt-6 flex items-center justify-between p-3.5 bg-slate-50 border border-slate-200/60 rounded-2xl shadow-inner">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white border border-slate-200 shadow-sm">
                                    <span :class="['h-2 w-2 rounded-full', bentoLiveApprovalGate ? 'bg-emerald-500' : 'bg-slate-400']"></span>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold text-slate-900 uppercase">Approval Gate</div>
                                    <div class="text-[9px] text-slate-400 font-bold font-mono">{{ bentoLiveApprovalGate ? 'ACTIVE CONTROL' : 'BYPASS ACTIVE' }}</div>
                                </div>
                            </div>
                            <button 
                                @click="bentoLiveApprovalGate = !bentoLiveApprovalGate"
                                :class="[bentoLiveApprovalGate ? 'bg-blue-600 shadow-md shadow-blue-500/10 hover:bg-blue-700' : 'bg-slate-800 hover:bg-slate-700', 'px-4 py-1.5 rounded-xl text-xs font-semibold text-white transition-all active:scale-95']"
                            >
                                {{ bentoLiveApprovalGate ? 'Restrict' : 'Enable' }}
                            </button>
                        </div>
                    </div>
 
                    <!-- Feature: Workflow Automation -->
                    <div class="group rounded-2xl border border-slate-200/60 bg-white hover:shadow-lg hover:border-blue-500/20 p-8 transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 mb-6 group-hover:scale-105 transition-transform duration-300">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2v20M2 12h20" stroke="currentColor" stroke-width="1.8" stroke-dasharray="2 2" />
                                    <path d="M12 8a4 4 0 100 8 4 4 0 000-8z" fill="currentColor" />
                                </svg>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">n8n Automation</h3>
                            <p class="mt-2 text-xs text-slate-500 leading-relaxed">
                                Deploy visual workflows that integrate CRM tables, email servers, and LLM triage nodes.
                            </p>
                        </div>
                        <div class="mt-6 flex items-center justify-between p-2.5 bg-slate-50 border border-slate-100 rounded-xl">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Webhook Target</span>
                            <span class="text-[9px] font-mono font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-100 flex items-center gap-1">
                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                ACTIVE NODE
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Lead Capture Form Section -->
        <section id="contact" class="py-16 md:py-24 border-t border-slate-200/40 relative">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
                    <!-- Left: Copy -->
                    <div class="space-y-6">
                        <div class="space-y-4">
                            <span class="text-[10px] font-mono font-bold tracking-widest text-blue-600 uppercase">GET IN TOUCH</span>
                            <h2 class="text-3xl font-extrabold font-display text-slate-900 tracking-tight leading-tight">
                                See it in action
                            </h2>
                            <p class="text-xs md:text-sm text-slate-500 leading-relaxed max-w-md">
                                Submit your info below. This form posts directly to our n8n webhook — your lead will be scored by AI in real-time and appear in the admin dashboard instantly.
                            </p>
                        </div>

                        <!-- Visual Webhook Pipeline Diagram -->
                        <div class="p-5 rounded-2xl bg-white border border-slate-200/60 shadow-sm relative overflow-hidden select-none">
                            <div class="absolute inset-0 bg-dot-pattern opacity-40 pointer-events-none"></div>
                            <div class="relative z-10 flex items-center justify-between gap-1.5 max-w-md mx-auto">
                                
                                <!-- Node 1: Form Submit -->
                                <div class="flex flex-col items-center gap-1.5 shrink-0">
                                    <div class="h-9 w-9 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shadow-sm">
                                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M19 4H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2zM7 8h10M7 12h10M7 16h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                        </svg>
                                    </div>
                                    <span class="text-[8px] font-bold font-mono text-slate-400 uppercase">1. SUBMIT</span>
                                </div>

                                <!-- Connector Line 1 -->
                                <div class="flex-1 h-0.5 border-t border-dashed border-slate-200 relative min-w-[20px]">
                                    <div class="absolute -top-1.5 left-1/2 h-3.5 w-3.5 -translate-x-1/2 rounded-full bg-blue-50 border border-blue-100/60 flex items-center justify-center">
                                        <div class="h-1.5 w-1.5 rounded-full bg-blue-500 animate-ping"></div>
                                    </div>
                                </div>

                                <!-- Node 2: n8n Node -->
                                <div class="flex flex-col items-center gap-1.5 shrink-0">
                                    <div class="h-9 w-9 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shadow-sm">
                                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 2v20M2 12h20" stroke="currentColor" stroke-width="1.8" stroke-dasharray="1.5 1.5" />
                                            <circle cx="12" cy="12" r="3" fill="currentColor" />
                                        </svg>
                                    </div>
                                    <span class="text-[8px] font-bold font-mono text-slate-400 uppercase">2. WEBHOOK</span>
                                </div>

                                <!-- Connector Line 2 -->
                                <div class="flex-1 h-0.5 border-t border-dashed border-slate-200 min-w-[20px]"></div>

                                <!-- Node 3: AI Engine -->
                                <div class="flex flex-col items-center gap-1.5 shrink-0">
                                    <div class="h-9 w-9 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shadow-sm">
                                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8" />
                                            <path d="M12 8v8M8 12h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                        </svg>
                                    </div>
                                    <span class="text-[8px] font-bold font-mono text-slate-400 uppercase">3. TRIAGE</span>
                                </div>

                                <!-- Connector Line 3 -->
                                <div class="flex-1 h-0.5 border-t border-dashed border-slate-200 min-w-[20px]"></div>

                                <!-- Node 4: DB Sync -->
                                <div class="flex flex-col items-center gap-1.5 shrink-0">
                                    <div class="h-9 w-9 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shadow-sm">
                                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4 6c0 1.66 3.58 3 8 3s8-1.34 8-3M4 12c0 1.66 3.58 3 8 3s8-1.34 8-3M4 18c0 1.66 3.58 3 8 3s8-1.34 8-3" stroke="currentColor" stroke-width="1.8" />
                                        </svg>
                                    </div>
                                    <span class="text-[8px] font-bold font-mono text-slate-400 uppercase">4. SYNC</span>
                                </div>
                            </div>
                        </div>

                        <!-- Compact feature list -->
                        <div class="space-y-2.5 pt-2">
                            <div class="flex items-center gap-2.5 text-xs text-slate-600">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                </span>
                                AI scores your lead instantly (Hot / Warm / Cold)
                            </div>
                            <div class="flex items-center gap-2.5 text-xs text-slate-600">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                </span>
                                Deduplication prevents duplicate records
                            </div>
                            <div class="flex items-center gap-2.5 text-xs text-slate-600">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                </span>
                                Admin gets notified via email
                            </div>
                        </div>
                    </div>

                    <!-- Right: Form -->
                    <div class="rounded-2xl border border-slate-200/60 bg-white p-6 shadow-lg">
                        <form @submit.prevent="submitLeadForm" class="space-y-4">
                            <div>
                                <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400 block mb-1.5">Full Name *</label>
                                <input
                                    v-model="leadForm.name"
                                    type="text"
                                    required
                                    placeholder="Jane Smith"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs text-slate-900 placeholder-slate-400 transition-all duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
                                />
                            </div>
                            <div>
                                <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400 block mb-1.5">Email *</label>
                                <input
                                    v-model="leadForm.email"
                                    type="email"
                                    required
                                    placeholder="jane@company.com"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs text-slate-900 placeholder-slate-400 transition-all duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
                                />
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400 block mb-1.5">Company</label>
                                    <input
                                        v-model="leadForm.company"
                                        type="text"
                                        placeholder="Acme Inc"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs text-slate-900 placeholder-slate-400 transition-all duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
                                    />
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400 block mb-1.5">Phone</label>
                                    <input
                                        v-model="leadForm.phone"
                                        type="tel"
                                        placeholder="+1 555 000 0000"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs text-slate-900 placeholder-slate-400 transition-all duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400 block mb-1.5">Message</label>
                                <textarea
                                    v-model="leadForm.message"
                                    rows="3"
                                    placeholder="Tell us about your needs..."
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs text-slate-900 placeholder-slate-400 transition-all duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none resize-none"
                                ></textarea>
                            </div>

                            <!-- Success Message -->
                            <div v-if="leadFormSuccess" class="rounded-xl bg-emerald-50 border border-emerald-100 p-3 text-xs font-medium text-emerald-700 flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                Lead captured! AI scoring is processing your submission.
                            </div>

                            <!-- Error Message -->
                            <div v-if="leadFormError" class="rounded-xl bg-red-50 border border-red-100 p-3 text-xs font-medium text-red-700">
                                {{ leadFormError }}
                            </div>

                            <button
                                type="submit"
                                :disabled="leadFormSubmitting"
                                class="w-full rounded-full bg-blue-600 hover:bg-blue-700 px-6 py-3 text-xs font-bold text-white shadow-md shadow-blue-500/10 hover:shadow-blue-500/25 transition-all duration-300 disabled:opacity-50 flex items-center justify-center gap-2"
                            >
                                <svg v-if="leadFormSubmitting" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ leadFormSubmitting ? 'Submitting...' : 'Submit Lead' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-slate-200 bg-white py-6 relative z-10">
            <div class="mx-auto max-w-5xl px-4 sm:px-6">
                <div class="flex flex-col items-center justify-between gap-6 sm:flex-row">
                    <!-- Brand -->
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 shadow-sm">
                            <svg class="h-4.5 w-4.5 text-white" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <polygon points="50,12 88,34 88,78 50,100 12,78 12,34" stroke="currentColor" stroke-width="8" stroke-linejoin="round"/>
                                <path d="M50,12 L50,56 L88,78 M50,56 L12,78" stroke="currentColor" stroke-width="6" stroke-linejoin="round"/>
                                <circle cx="50" cy="56" r="10" fill="currentColor"/>
                            </svg>
                        </div>
                        <span class="text-xs font-bold font-display uppercase tracking-widest text-slate-900">AI Ops</span>
                    </div>

                    <!-- Links -->
                    <div class="flex flex-wrap justify-center gap-6 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                        <a href="#how-it-works" class="hover:text-slate-900 transition-colors">How it works</a>
                        <a href="#features" class="hover:text-slate-900 transition-colors">Features</a>
                        <a href="https://github.com/jaor13/ai-customer-ops-platform" target="_blank" class="hover:text-slate-900 transition-colors">Repository</a>
                    </div>

                    <!-- Copyright -->
                    <p class="text-[10px] text-slate-500">
                        &copy; 2026 AI Ops. Built with Laravel, Vue, & Qdrant.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Scoped keyframes for robust 3D/Float animations */
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

@keyframes float-top {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-16px); }
}

@keyframes float-mid {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-8px); }
}

@keyframes float-bot {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-3px); }
}

.animate-float {
    animation: float 6s infinite ease-in-out;
}

.animate-float-top {
    animation: float-top 6s infinite ease-in-out;
    transform-box: fill-box;
    transform-origin: center;
}

.animate-float-mid {
    animation: float-mid 6s infinite ease-in-out;
    transform-box: fill-box;
    transform-origin: center;
}

.animate-float-bot {
    animation: float-bot 6s infinite ease-in-out;
    transform-box: fill-box;
    transform-origin: center;
}

/* Translucent slide fades */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease-in-out;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
