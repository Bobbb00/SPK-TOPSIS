<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '../Layouts/AuthenticatedLayout.vue';
import StatCards from '../Components/Dashboard/StatCards.vue';
import TopRankingCard from '../Components/Dashboard/TopRankingCard.vue';
import CriteriaWeightCard from '../Components/Dashboard/CriteriaWeightCard.vue';
import QuotaModal from '../Components/Quota/QuotaModal.vue';
import AiExplanationModal from '../Components/Ranking/AiExplanationModal.vue';

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
    top_rankings: {
        type: Array,
        default: () => [],
    },
    criterias: {
        type: Array,
        default: () => [],
    },
});

const isRecalculating = ref(false);
const showQuotaModal = ref(false);
const showAiModal = ref(false);
const selectedApplicant = ref(null);

const passedCount = computed(() => {
    return props.top_rankings.filter(r => r.is_passed).length;
});

const handleExplainAi = (applicant) => {
    selectedApplicant.value = applicant;
    showAiModal.value = true;
};

const handleRecalculate = () => {
    isRecalculating.value = true;
    router.reload({
        onFinish: () => {
            setTimeout(() => {
                isRecalculating.value = false;
            }, 600);
        },
    });
};
</script>

<template>
    <Head title="Dashboard Ringkasan" />

    <AuthenticatedLayout title="Dashboard Seleksi Beasiswa">
        <div class="space-y-6">
            <!-- Header Area -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                        <span class="rounded bg-[#00236f] text-white px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider">
                            Modul 5 • Overview SPK
                        </span>
                        <span class="flex items-center gap-1.5 text-slate-500 text-xs font-medium">
                            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>SPK-TOPSIS Engine Aktif</span>
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">
                        Dashboard Seleksi Beasiswa Prestasi & Afirmasi
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Tahun Akademik {{ stats.quota?.period || '2026/2027' }} • Kuota {{ stats.quota?.quota_limit ?? 5 }} Penerima
                    </p>
                </div>

                <!-- Action Controls -->
                <div class="flex items-center gap-2.5 flex-wrap">
                    <button
                        @click="showQuotaModal = true"
                        type="button"
                        class="flex items-center gap-1.5 px-3 py-2 bg-white text-slate-700 text-xs font-semibold rounded-lg border border-slate-200 shadow-2xs hover:bg-slate-50 transition cursor-pointer"
                        title="Klik untuk mengubah periode atau kuota penerima"
                    >
                        <span class="material-symbols-outlined text-[17px] text-blue-600">calendar_month</span>
                        <span>Periode: {{ stats.quota?.period || '2026/2027' }}</span>
                        <span class="material-symbols-outlined text-[15px] text-slate-400">edit</span>
                    </button>

                    <a
                        href="/ranking/export-csv"
                        class="flex items-center gap-1.5 px-3 py-2 bg-white text-slate-700 text-xs font-semibold rounded-lg border border-slate-200 shadow-2xs hover:bg-slate-50 transition"
                    >
                        <span class="material-symbols-outlined text-[17px] text-blue-600">file_download</span>
                        <span>Unduh Rekap Cepat</span>
                    </a>

                    <button
                        @click="handleRecalculate"
                        :disabled="isRecalculating"
                        class="flex items-center gap-1.5 px-3 py-2 bg-[#0051d5] text-white text-xs font-semibold rounded-lg shadow-2xs hover:bg-[#00236f] transition disabled:opacity-70 cursor-pointer"
                    >
                        <span :class="{ 'animate-spin': isRecalculating }" class="material-symbols-outlined text-[17px]">sync</span>
                        <span>Hitung Ulang TOPSIS</span>
                    </button>
                </div>
            </div>

            <!-- 4 Stat Cards -->
            <StatCards
                :stats="stats"
                :passed-count="passedCount"
                @edit-quota="showQuotaModal = true"
            />

            <!-- 2 Columns Grid: Top 5 Ranking (60%) : Criteria Distribution (40%) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <div class="lg:col-span-7">
                    <TopRankingCard
                        :rankings="top_rankings"
                        :total-applicants="stats.applicant_count"
                        :is-ready="stats.is_ready_for_calculation"
                        @explain-ai="handleExplainAi"
                    />
                </div>
                <div class="lg:col-span-5">
                    <CriteriaWeightCard
                        :criterias="criterias"
                        :total-weight="stats.total_weight"
                        :period="stats.quota?.period ? `Periode ${stats.quota.period}` : 'Periode Genap 2025/2026'"
                    />
                </div>
            </div>

            <!-- Notice Banner Transparansi & Integritas Audit -->
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-2xs">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div class="flex items-start gap-4 max-w-4xl">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#00236f] text-white">
                            <span class="material-symbols-outlined text-[22px]">policy</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900">
                                    Transparansi Algoritma Terisolasi • Audit Trail Siap Diuji
                                </h3>
                                <span class="rounded bg-teal-50 px-1.5 py-0.5 text-[10px] font-bold text-teal-700">
                                    Tervalidasi TOPSIS Standard
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-slate-500 leading-relaxed">
                                Seluruh tahap komputasi normalisasi matriks, pembobotan terarah, penentuan solusi ideal positif (<strong class="font-mono text-slate-800">A+</strong>) dan negatif (<strong class="font-mono text-slate-800">A-</strong>), hingga perhitungan jarak Euclidean dieksekusi secara otomatis melalui <em>Laravel Dedicated Service Pattern</em>. Integritas data terjamin bebas distorsi komputasi manual.
                            </p>
                        </div>
                    </div>
                    <Link
                        href="/ranking"
                        class="shrink-0 inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-semibold text-slate-800 shadow-2xs hover:bg-slate-100 transition"
                    >
                        <span class="material-symbols-outlined text-[17px] text-blue-600">find_in_page</span>
                        <span>Buka Transparansi Matriks</span>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <QuotaModal
            :show="showQuotaModal"
            :quota="stats.quota"
            @close="showQuotaModal = false"
        />

        <AiExplanationModal
            :show="showAiModal"
            :applicant="selectedApplicant"
            @close="showAiModal = false"
        />
    </AuthenticatedLayout>
</template>
